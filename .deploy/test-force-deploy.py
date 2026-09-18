"""Isolated Git integration tests; PHP lint and ownership changes are stubbed.

Runs only against temporary repositories, never /var/www/daisho.
"""
import os
from pathlib import Path
import subprocess
import tarfile
import tempfile
import unittest


class ForceDeployTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.addCleanup(self.temp.cleanup)
        self.root = Path(self.temp.name)
        self.env = dict(os.environ, GIT_AUTHOR_NAME='Test', GIT_AUTHOR_EMAIL='test@example.invalid',
                        GIT_COMMITTER_NAME='Test', GIT_COMMITTER_EMAIL='test@example.invalid')
        self.remote = self.root / 'remote.git'
        self.seed = self.root / 'seed'
        self.repo = self.root / 'production'
        self.run_cmd('git', 'init', '--bare', str(self.remote))
        self.run_cmd('git', 'init', '-b', 'main', str(self.seed))
        (self.seed / '.deploy/global-daisho/web').mkdir(parents=True)
        (self.seed / '.deploy/global-daisho/web/index.php').write_text('<?php echo "test";')
        (self.seed / 'tracked.txt').write_text('original\n')
        (self.seed / '.gitignore').write_text('ignored.txt\n')
        self.git(self.seed, 'add', '.')
        self.git(self.seed, 'commit', '-m', 'Initial')
        self.git(self.seed, 'remote', 'add', 'origin', str(self.remote))
        self.git(self.seed, 'push', 'origin', 'main')
        self.run_cmd('git', 'clone', '-b', 'main', str(self.remote), str(self.repo))
        (self.seed / 'tracked.txt').write_text('remote\n')
        (self.seed / 'collision.txt').write_text('remote collision\n')
        self.git(self.seed, 'add', '.')
        self.git(self.seed, 'commit', '-m', 'Remote update')
        self.git(self.seed, 'push', 'origin', 'main')
        self.target = self.git(self.seed, 'rev-parse', 'HEAD').stdout.strip()
        (self.root / 'key').touch()
        self.backups = self.root / 'backups'
        source = (Path(__file__).parent / 'global-daisho/bin/global-daisho-deploy').read_text()
        replacements = {
            "readonly REPO_ROOT='/var/www/daisho'": f"readonly REPO_ROOT='{self.repo}'",
            "readonly EXPECTED_ORIGIN='git@github.com:Taiju-h/global-daisho.git'": f"readonly EXPECTED_ORIGIN='{self.remote}'",
            "readonly SSH_KEY='/home/heartf/.ssh/global_daisho_github_ed25519'": f"readonly SSH_KEY='{self.root / 'key'}'",
            "readonly RUNTIME_DIR='/var/www/global-daisho-deployer'": f"readonly RUNTIME_DIR='{self.root / 'runtime'}'",
            "readonly LOCK_FILE='/run/lock/global-daisho-deployer.lock'": f"readonly LOCK_FILE='{self.root / 'lock'}'",
            "readonly BACKUP_ROOT='/var/backups/global-daisho-deployer/force'": f"readonly BACKUP_ROOT='{self.backups}'",
        }
        for old, new in replacements.items():
            assert old in source
            source = source.replace(old, new)
        self.helper = self.root / 'helper'
        self.helper.write_text(source)
        stubs = self.root / 'stubs'
        stubs.mkdir()
        for name, text in {
            'php': '#!/bin/sh\nexit 0\n',
            'install': '#!/bin/bash\nargs=(); while (($#)); do case "$1" in -o|-g) shift 2;; *) args+=("$1"); shift;; esac; done\nexec /usr/bin/install "${args[@]}"\n',
        }.items():
            file = stubs / name
            file.write_text(text)
            file.chmod(0o755)
        self.env['PATH'] = str(stubs) + ':' + self.env['PATH']

    def run_cmd(self, *args, check=True):
        return subprocess.run(args, env=self.env, text=True, capture_output=True, check=check)

    def git(self, repo, *args):
        return self.run_cmd('git', '-C', str(repo), *args)

    def action(self, action):
        return self.run_cmd('bash', str(self.helper), action, check=False)

    def test_dirty_diverged_force_backup_and_recovery(self):
        (self.repo / 'local-only.txt').write_text('local commit\n')
        self.git(self.repo, 'add', '.')
        self.git(self.repo, 'commit', '-m', 'Local only')
        before = self.git(self.repo, 'rev-parse', 'HEAD').stdout.strip()
        (self.repo / 'tracked.txt').write_text('staged\n')
        self.git(self.repo, 'add', 'tracked.txt')
        (self.repo / 'tracked.txt').write_text('unstaged\n')
        (self.repo / 'collision.txt').write_text('local untracked collision\n')
        (self.repo / 'ignored.txt').write_text('private local settings\n')
        self.assertNotEqual(self.action('production').returncode, 0)
        self.assertEqual(self.action('diff').returncode, 0)
        result = self.action('force-production')
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.assertEqual(self.git(self.repo, 'rev-parse', 'HEAD').stdout.strip(), self.target)
        self.assertEqual((self.repo / 'tracked.txt').read_text(), 'remote\n')
        self.assertEqual((self.repo / 'ignored.txt').read_text(), 'private local settings\n')
        backup = next(self.backups.iterdir())
        self.assertEqual(backup.stat().st_mode & 0o777, 0o700)
        with tarfile.open(backup / 'worktree.tar') as archive:
            self.assertNotIn('./.git', archive.getnames())
            self.assertEqual(archive.extractfile('./collision.txt').read(), b'local untracked collision\n')
            self.assertEqual(archive.extractfile('./ignored.txt').read(), b'private local settings\n')
        self.git(self.repo, 'bundle', 'verify', str(backup / 'repository.bundle'))
        self.git(self.repo, 'reset', '--hard', before)
        self.run_cmd('tar', '-xpf', str(backup / 'worktree.tar'), '-C', str(self.repo))
        self.run_cmd('cp', '-p', str(backup / 'index'), str(self.repo / '.git/index'))
        self.assertEqual((self.repo / 'tracked.txt').read_text(), 'unstaged\n')
        self.assertEqual(self.git(self.repo, 'show', ':tracked.txt').stdout, 'staged\n')
        self.assertEqual((self.repo / 'collision.txt').read_text(), 'local untracked collision\n')

    def test_failed_backup_never_resets(self):
        before = self.git(self.repo, 'rev-parse', 'HEAD').stdout
        (self.repo / 'tracked.txt').write_text('keep me\n')
        stub = self.root / 'stubs/tar'
        stub.write_text('#!/bin/sh\nexit 2\n')
        stub.chmod(0o755)
        result = self.action('force-production')
        self.assertNotEqual(result.returncode, 0)
        self.assertEqual(self.git(self.repo, 'rev-parse', 'HEAD').stdout, before)
        self.assertEqual((self.repo / 'tracked.txt').read_text(), 'keep me\n')

    def test_failed_fetch_never_resets(self):
        self.remote.rename(self.root / 'offline.git')
        before = self.git(self.repo, 'rev-parse', 'HEAD').stdout
        result = self.action('force-production')
        self.assertNotEqual(result.returncode, 0)
        self.assertEqual(self.git(self.repo, 'rev-parse', 'HEAD').stdout, before)
        self.assertFalse(self.backups.exists())

    def test_status_and_regular_deploy(self):
        self.assertIn('FORCE_SUPPORTED=1', self.action('status').stdout)
        self.assertEqual(self.action('production').returncode, 0)
        self.assertEqual(self.git(self.repo, 'rev-parse', 'HEAD').stdout.strip(), self.target)

    def test_target_lint_failure_stops_before_reset(self):
        (self.root / 'stubs/php').write_text('#!/bin/sh\nexit 1\n')
        before = self.git(self.repo, 'rev-parse', 'HEAD').stdout
        self.assertNotEqual(self.action('force-production').returncode, 0)
        self.assertEqual(self.git(self.repo, 'rev-parse', 'HEAD').stdout, before)

    def prepare_updater(self):
        for name, content in {
            'bin/global-daisho-deploy': '#!/bin/bash\necho new-helper\n',
            'sudoers.d/global-daisho-deployer': 'new-sudoers\n',
        }.items():
            file = self.seed / '.deploy/global-daisho' / name
            file.parent.mkdir(parents=True, exist_ok=True)
            file.write_text(content)
        self.git(self.seed, 'add', '.')
        self.git(self.seed, 'commit', '-m', 'Runtime sources')
        self.git(self.seed, 'push', 'origin', 'main')
        for name in ['installed-helper', 'installed-web', 'installed-sudoers']:
            (self.root / name).write_text('old-' + name)
        stub = self.root / 'stubs/visudo'
        stub.write_text('#!/bin/sh\nexit 0\n')
        stub.chmod(0o755)
        source = (Path(__file__).parent / 'update-global-daisho-deployer.sh').read_text()
        for old, new in {
            '/var/www/daisho': str(self.repo),
            'git@github.com:Taiju-h/global-daisho.git': str(self.remote),
            '/home/heartf/.ssh/global_daisho_github_ed25519': str(self.root / 'key'),
            '/usr/local/sbin/global-daisho-deploy': str(self.root / 'installed-helper'),
            '/var/www/global-daisho-deployer/index.php': str(self.root / 'installed-web'),
            '/etc/sudoers.d/global-daisho-deployer': str(self.root / 'installed-sudoers'),
            '/run/lock/global-daisho-deployer.lock': str(self.root / 'lock'),
            '/var/backups/global-daisho-deployer/runtime': str(self.root / 'runtime-backups'),
        }.items():
            source = source.replace(old, new)
        updater = self.root / 'updater'
        updater.write_text(source)
        return updater

    def test_updater_leaves_dirty_production_untouched(self):
        updater = self.prepare_updater()
        before = self.git(self.repo, 'rev-parse', 'HEAD').stdout
        (self.repo / 'tracked.txt').write_text('keep dirty production\n')
        result = self.run_cmd('bash', str(updater), check=False)
        self.assertEqual(result.returncode, 0, result.stdout + result.stderr)
        self.assertEqual(self.git(self.repo, 'rev-parse', 'HEAD').stdout, before)
        self.assertEqual((self.repo / 'tracked.txt').read_text(), 'keep dirty production\n')
        self.assertIn('new-helper', (self.root / 'installed-helper').read_text())
        self.assertEqual((self.root / 'installed-sudoers').read_text(), 'new-sudoers\n')

    def test_updater_failed_install_restores_runtime(self):
        updater = self.prepare_updater()
        stub = self.root / 'stubs/install'
        contents = stub.read_text()
        contents = contents.replace('args=();', '[[ "${!#}" == *installed-web ]] && exit 1\nargs=();')
        stub.write_text(contents)
        result = self.run_cmd('bash', str(updater), check=False)
        self.assertNotEqual(result.returncode, 0)
        for name in ['installed-helper', 'installed-web', 'installed-sudoers']:
            self.assertEqual((self.root / name).read_text(), 'old-' + name)


if __name__ == '__main__':
    unittest.main()
