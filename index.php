<?php
$binaries_dir = __DIR__."/binaries";
$binaries = array_reverse(scandir($binaries_dir));

function platformName($p)
{
  switch ($p) {
    case "macosx": return "Mac OS X";
    case "linux": return "Linux";
    case "win": return "Windows";
    default: return $p;
  }
}

$platforms = array("macosx", "linux", "win");
$versions_stable = array();
$versions_master = array();
foreach ($binaries as $b)
{
  if (preg_match('/^openskyscraper-([^-]*)-([^-]*)\.tar\.bz2$/sm', $b, $matches)) {
    $e = new stdClass;
    $e->name = $b;
    $e->url = "/binaries/".urlencode($b);
    $e->version = $matches[1];
    $e->platform = $matches[2];
    $versions_stable[$e->version][$e->platform] = $e;
    if (!in_array($e->platform, $platforms))
      $platforms[] = $e->platform;
  }
  if (preg_match('/^openskyscraper-(\d{4})(\d{2})(\d{2})-(\d{2})(\d{2})-([0-9a-f]*)-([^-]*)\.tar\.bz2$/sm', $b, $matches)) {
    $e = new stdClass;
    $e->name = $b;
    $e->url = "/binaries/".urlencode($b);
    $e->date = $matches[1]."-".$matches[2]."-".$matches[3]." ".$matches[4].":".$matches[5];
    $e->commit = $matches[6];
    $e->platform = $matches[7];
    $versions_master[$e->date]["commit"] = $e->commit;
    $versions_master[$e->date]["platforms"][$e->platform] = $e;
    if (!in_array($e->platform, $platforms))
      $platforms[] = $e->platform;
  }
}

// Find the latest stable and master release per platform.
$latest_stable = array();
$latest_master = array();
foreach ($platforms as $p) {
  foreach ($versions_stable as $v => $ps) {
    if (isset($ps[$p])) {
      $latest_stable[$p] = $ps[$p];
      break;
    }
  }
  foreach ($versions_master as $v => $ps) {
    if (isset($ps["platforms"][$p])) {
      $latest_master[$p] = $ps["platforms"][$p];
      break;
    }
  }
}
?>
<html>
  <head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="/css/style.css" type="text/css" />
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-45648401-1', 'openskyscraper.org');
      ga('send', 'pageview');
    </script>
  </head>
  <body>
    <div class="mw">
      <div id="title">OpenSkyscraper</div>
      <div id="subtitle">Welcome to OpenSkyscraper, an open source clone of Maxis' classic game SimTower.</div>
    </div>
    
    <div class="mw">
      <h1>Latest Release</h1>
      <div id="versions" style="text-align: center;">
        <?php foreach ($latest_stable as $p => $e) {
          echo "<a href=\"{$e->url}\" class=\"ver\">";
          echo "<div class=\"v\">{$e->version}</div>";
          echo "<div class=\"pf\">for ".htmlspecialchars(platformName($p))."</div>";
          echo "</a>";
        } ?>
      </div>
      
      <p>If you wish to contribute to the project, fork <a href="https://github.com/fabianschuiki/OpenSkyscraper">OpenSkyscraper on GitHub</a> and start hacking away! Bug reports and ideas are always welcome there as well.</p>
      
      <h2>Master Builds</h2>
      <div id="masterbuilds" style="text-align: center;">
        <?php foreach ($latest_master as $p => $e) {
          echo "<a href=\"{$e->url}\" class=\"mb\">";
          echo "<div class=\"d\">{$e->date}</div>";
          echo "<div class=\"v\">{$e->commit}</div>";
          echo "<div class=\"pf\">for ".htmlspecialchars(platformName($p))."</div>";
          echo "</a>";
        } ?>
      </div>
      
    </div>
    <div class="mw">
      <h1>Version Archive</h1>
      <p>Refer to the <a href="/ci/">Build Logs</a> for detailed feedback about the automated continuous integration builds.</p>
      
      <h2>Stable Releases</h2>
      <table class="varch">
        <tr>
          <th>Version</th>
          <?php foreach ($platforms as $p) echo "<th>".htmlspecialchars(platformName($p))."</th>"; ?>
        </tr>
        <?php 
          foreach ($versions_stable as $v => $ps) {
            echo "<tr><td>".htmlspecialchars($v)."</td>\n";
            foreach ($platforms as $p) {
              $e = @$ps[$p];
              if ($e) {
                echo "<td><a href=\"/binaries/{$e->name}\">".htmlspecialchars(platformName($p))."</a></td>\n";
              } else {
                echo "<td class=\"inv\">—</td>\n";
              }
            }
            echo "</tr>\n";
          }
        ?>
      </table>
      <h2>Master Builds</h2>
      <table>
        <tr>
          <th>Date/Time</th>
          <th>Commit</th>
          <?php foreach ($platforms as $p) echo "<th>".htmlspecialchars(platformName($p))."</th>"; ?>
        </tr>
        <?php
          foreach ($versions_master as $v => $ps) {
            echo "<tr>";
            echo "<td>".htmlspecialchars($v)."</td>";
            echo "<td>".htmlspecialchars($ps["commit"])."</td>";
            foreach ($platforms as $p) {
              $e = @$ps["platforms"][$p];
              if ($e) {
                echo "<td><a href=\"/binaries/{$e->name}\">".htmlspecialchars(platformName($p))."</a></td>\n";
              } else {
                echo "<td class=\"inv\">—</td>\n";
              }
            }
            echo "</tr>\n";
          }
        ?>
      </table>
    </div>
  </body>
</html>