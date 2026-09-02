<?php
$enja = (preg_match('/en\./', $_SERVER['SERVER_NAME'])) 
    ? array('domain' => '', 'show' => '日本語ページへ') 
    : array('domain' => 'en.', 'show' => 'English Page');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Daisho Chemical R&D</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body {
      margin: 0;
      font-family: "Yu Mincho", "游明朝", "Times New Roman", serif;
    }
    header {
      border-bottom: 1px solid #ccc;
    }
    .header-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 8px 20px;
      background: #fff;
    }
    .logo {
      font-size: 20px;
      color: #2a3d59;
      font-weight: bold;
    }
    .contact {
      font-size: 14px;
      color: #555;
    }
    .contact span {
      color: #000099;
      font-weight: bold;
      font-size: 18px;
    }
    .eng-button {
      margin-left: 10px;
      font-size: 12px;
      padding: 2px 6px;
      border: 1px solid #999;
      background-color: #f8f8f8;
      cursor: pointer;
    }
    nav {
      display: flex;
      justify-content: center;
      background: #f5f5f5;
      border-top: 1px solid #ddd;
      border-bottom: 1px solid #ccc;
    }
    nav a {
      padding: 12px 20px;
      font-size: 14px;
      color: #333;
      text-decoration: none;
      font-weight: bold;
      border-right: 1px solid #bbb;
    }
    nav a:last-child {
      border-right: none;
    }
    nav a:hover {
      background-color: #e0e0e0;
    }
    .clr {
      clear: both;
    }
  </style>
  <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;
      i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},
      i[r].l=1*new Date();
      a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];
      a.async=1;
      a.src=g;
      m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    
    ga('create', 'UA-60395373-1', 'auto');
    ga('send', 'pageview');
  </script>
</head>
<body>
<div id="wrapper">
  <header>
    <div class="header-top">
      <div class="logo"><a href="/">Daisho Chemical R&D</a></div>
      <div class="contact">
        For product inquiries: <span>+81-3-6801-6018</span>
        <a href="https://www.daishokagaku.com/" class="eng-button">
          日本語
        </a>
      </div>
    </div>
    <nav>
      <a href="/aboutus/">Company Overview</a>
      <a href="/research/">R&D</a>
      <a href="/products/">Products</a>
      <a href="/patent/">Patents</a>
      <a href="/client/">Major Clients</a>
      <a href="/recruit/">Careers</a>
    </nav>
  </header>

  <div class="clr">&nbsp;</div>
