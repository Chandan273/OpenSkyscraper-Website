<?php
$binaries_dir = __DIR__."/../binaries";
$binaries = scandir($binaries_dir);

$builds = array();
$platforms = array();
foreach ($binaries as $b)
{
  if (preg_match('/^openskyscraper-(.*?)-([^-]*)\.log$/sm', $b, $matches)) {
    $e = new stdClass;
    $e->name = $b;
    $e->url = "/binaries/".urlencode($b);
    $e->version = $matches[1];
    $e->platform = $matches[2];
    $e->path = $binaries_dir."/".$b;
    $e->date = filemtime($e->path);
    
    $f = fopen($e->path, "r");
    $input = fread($f, 128);
    if (preg_match('/^# Build (.+) \((\d+) seconds/', $input, $matches)) {
      switch ($matches[1]) {
        case "failed": $e->state = "n"; break;
        case "passed": $e->state = "p"; break;
        default: $e->state = "x"; break;
      }
      $e->duration = $matches[2];
    } else {
      $e->state = "?";
    }
    fclose($f);
        
    $builds[$e->version][$e->platform] = $e;
    if (!in_array($e->platform, $platforms))
      $platforms[] = $e->platform;
  }
}
uksort($builds, function($a,$b){
  $da = array_pop($a)->date;
  $db = array_pop($b)->date;
  if ($da == $db) return 0;
  return $da < $db ? -1 : 1;
});

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
      <div id="subtitle">Build Logs</div>
    </div>
    
    <div class="mw">
      <table>
        <tr>
          <th>Version</th>
          <?php foreach ($platforms as $p) echo "<th>".htmlspecialchars($p)."</th>\n"; ?>
        </tr>
        <?php foreach ($builds as $v => $ps) {
          echo "<tr>";
          echo "<td>".htmlspecialchars($v)."</td>";
          foreach ($platforms as $p) {
            if (isset($ps[$p])) {
              $e = $ps[$p];
              echo "<td class=\"st {$e->state}\"><a href=\"{$e->url}\">";
              echo "<span class=\"sym\">";
              switch ($e->state) {
                case "p": echo "\xE2\x9C\x94"; break;
                case "n": echo "\xE2\x9C\x98"; break;
                case "x": echo "?"; break;
              }
              echo "</span><br/>{$e->duration}s";// on ".date("Y-m-d H:i", $e->date);
              echo "</a></td>";
            } else {
              echo "<td>—</td>";
            }
          }
          echo "</tr>";
        } ?>
      </table>
    </div>
  </body>
</html>