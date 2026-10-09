<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli' && !defined('CRM_MIGRATION_TEST')) { http_response_code(404); exit; }
require dirname(__DIR__).'/crm-migrate.php';
$checks=0;
function migration_check(bool $value,string $label): void { global $checks; if(!$value) throw new RuntimeException('FAIL: '.$label); $checks++; }
function migration_reject(callable $run,string $label): void { try { $run(); } catch(RuntimeException $e) { migration_check(true,$label); return; } throw new RuntimeException('FAIL: '.$label); }
foreach([
    ['CREATE TABLE x (id INT);','ddl'],
    ['ALTER TABLE x ADD COLUMN note TEXT','ddl'],
    ['CREATE UNIQUE INDEX ix ON x(id);','ddl'],
    ["-- Comment\nINSERT INTO x(note) VALUES('a;b'); -- trailing comment",'dml'],
    ["UPDATE x SET note='it''s okay; yes' WHERE id=1;",'dml'],
    ['DELETE FROM x WHERE id=1;','dml'],
    ['/* normal comment */ CREATE TABLE `x;y` (`a``b` INT);','ddl'],
    ["# comment\nCREATE TABLE x (id INT); /* trailing */",'ddl'],
] as [$sql,$kind]) migration_check(crm_migration_sql($sql,$kind)===$sql,'valid SQL preserved');
foreach([
    ['CREATE TABLE x(id INT); DROP TABLE y;','ddl'],
    ['UPDATE x SET id=1; COMMIT;','dml'],
    ['/*!50000 DROP TABLE x */ CREATE TABLE y(id INT);','ddl'],
    ['/*+ hint */ CREATE TABLE y(id INT);','ddl'],
    ['CREATE TABLE x(id INT); /* unfinished','ddl'],
    ["UPDATE x SET note='unfinished",'dml'],
    ["UPDATE x SET note='back\\slash';",'dml'],
    ['USE another_database;','ddl'],
    ['BEGIN;','dml'],
    ['ALTER TABLE x ADD y INT;','dml'],
    ['UPDATE x SET id=1;','ddl'],
    ['-- comment only','dml'],
    ['','ddl'],
    [str_repeat('x',1048577),'ddl'],
    ['SELECT 1;','other'],
] as [$sql,$kind]) migration_reject(fn()=>crm_migration_sql($sql,$kind),'invalid/unsafe migration rejected');
$allFiles=crm_migration_files(dirname(__DIR__).'/crm-sql');
migration_check(count($allFiles)>=4,'all supplied schema files validated');
// Keep the plan scenarios stable as new numbered migrations are added.
$files=array_slice($allFiles,0,4,true);
$names=array_keys($files);
$history=[];
migration_check(count(crm_migration_plan($files,$history))===4,'new installation plans all files');
$first=$names[0]; $history[$first]=['checksum'=>$files[$first]['checksum'],'status'=>'applied'];
migration_check(count(crm_migration_plan($files,$history))===3,'applied file skipped');
$changed=$files; $changed[$first]['checksum']=str_repeat('0',64);
migration_reject(fn()=>crm_migration_plan($changed,$history),'changed applied SQL rejected before execution');
$removed=$files; unset($removed[$first]);
migration_reject(fn()=>crm_migration_plan($removed,$history),'missing recorded file rejected');
$history[$first]['status']='failed';
migration_reject(fn()=>crm_migration_plan($files,$history),'failed SQL does not auto-run');
migration_check(count(crm_migration_plan($files,$history,$first))===4,'explicit failed retry allowed');
$history[$first]['status']='running';
migration_reject(fn()=>crm_migration_plan($files,$history),'interrupted SQL does not auto-run');
migration_check(count(crm_migration_plan($files,$history,$first))===4,'explicit interrupted retry allowed');
$history[$first]['status']='applied';
migration_reject(fn()=>crm_migration_plan($files,$history,$first),'already applied retry rejected');
$history=[]; $last=$names[3]; $history[$last]=['checksum'=>$files[$last]['checksum'],'status'=>'applied'];
migration_reject(fn()=>crm_migration_plan($files,$history),'late insertion of older migration rejected');
$history=[]; foreach($files as $name=>$file) $history[$name]=['checksum'=>$file['checksum'],'status'=>'applied'];
migration_check(crm_migration_plan($files,$history)===[],'fully applied DB has no pending work');
$temp=sys_get_temp_dir().'/crm-migration-test-'.bin2hex(random_bytes(6)); mkdir($temp);
try {
    file_put_contents($temp.'/0001_one.ddl.sql','CREATE TABLE x(id INT);');
    file_put_contents($temp.'/0001_two.ddl.sql','CREATE TABLE y(id INT);');
    migration_reject(fn()=>crm_migration_files($temp),'duplicate sequence number rejected');
    unlink($temp.'/0001_two.ddl.sql');
    file_put_contents($temp.'/bad.sql','CREATE TABLE y(id INT);');
    migration_reject(fn()=>crm_migration_files($temp),'invalid filename rejected');
} finally { foreach(glob($temp.'/*')?:[] as $path) unlink($path); rmdir($temp); }
echo "PASS: $checks migration checks\n";
