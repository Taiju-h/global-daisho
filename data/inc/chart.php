   <style>
    .org-chart {
      display: flex;
      justify-content: center;
      margin-top: 40px;
      font-family: sans-serif;
    }

    .org-node {
      border: 1px solid #999;
      border-radius: 4px;
      padding: 8px 12px;
      margin: 10px auto;
      background-color: #f9f9f9;
      text-align: center;
      min-width: 160px;
      font-size: 14px;
      font-weight: bold;
    }

    .org-branch {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      margin-top: 20px;
      position: relative;
    }

    .org-branch::before {
      content: "";
      position: absolute;
      top: -20px;
      left: 50%;
      width: 2px;
      height: 20px;
      background: #999;
      transform: translateX(-50%);
    }

    .org-branch > .org-node {
      margin: 10px;
      position: relative;
    }

    .org-branch > .org-node::before {
      content: "";
      position: absolute;
      top: -20px;
      left: 50%;
      width: 2px;
      height: 20px;
      background: #999;
      transform: translateX(-50%);
    }

    .org-children {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      margin-top: 20px;
      position: relative;
    }

    .org-children::before {
      content: "";
      position: absolute;
      top: -20px;
      left: 50%;
      width: 2px;
      height: 20px;
      background: #999;
      transform: translateX(-50%);
    }

    .org-children > .org-node {
      margin: 10px 20px;
      position: relative;
    }

    .org-children > .org-node::before {
      content: "";
      position: absolute;
      top: -20px;
      left: 50%;
      width: 2px;
      height: 20px;
      background: #999;
      transform: translateX(-50%);
    }
  </style>
</head>
<body>
  <div class="org-chart">
    <div>
      <div class="org-node">Board of Directors</div>

      <div class="org-branch">
        <div>
          <div class="org-node">Civil Engineering & Chemical Division</div>
          <div class="org-children">
            <div class="org-node">R&D Department</div>
            <div class="org-node">Sales Department</div>
            <div class="org-node">Overseas Department</div>
          </div>
        </div>

        <div>
          <div class="org-node">Solutions Division</div>
          <div class="org-children">
            <div class="org-node">Information Systems Dept.</div>
            <div class="org-node">Administration Dept.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
 