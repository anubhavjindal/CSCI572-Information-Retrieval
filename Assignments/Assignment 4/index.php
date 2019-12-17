<?php
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;
if ($query)
{
  $choice = isset($_REQUEST['sort'])? $_REQUEST['sort'] : "Lucene";
  require_once('Apache/Solr/Service.php');
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/csci572/');
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }
  try
  {
    if($choice == "Lucene")
      $additionalParameters=array('sort' => '');
    else
      $additionalParameters=array('sort' => 'pageRankFile desc');
    $results = $solr->search($query, 0, $limit, $additionalParameters);
  }
  catch (Exception $e)
  {
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}?>
<html>
  <head>
    <title>CSCI-572 Comparing Search Engine Ranking Algorithms</title>
  </head>
  <body style='margin-left:30px; margin-right:30px;'>
    <form  accept-charset="utf-8" method="get" >
      <center>
        <h2>Comparing PageRank and Lucene</h2>
        <label for="q">Search:</label>
        <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
        <input type="submit" value="Submit"/><br/>
        <input type="radio" name="sort" value="pagerank" <?php if(isset($_REQUEST['sort'])&&$choice=="pagerank"){echo'checked="checked"';}?>>Page Rank
        <input type="radio" name="sort" value="Lucene" <?php if(isset($_REQUEST['sort'])&&$choice=="Lucene"){echo'checked="checked"';}?>>Lucene
      </center>
    </form>
    <?php
      $arrayFromCSV =  array_map('str_getcsv', file('/Users/anubhavjindal/Downloads/solr-8.0.0/URLtoHTML_yahoo_news.csv'));
      if ($results)
      {
        $total = (int) $results->response->numFound;
        $start = min(1, $total);
        $end = min($limit, $total);
        $stack = [];
        echo "  <div><i>Showing Results $start -  $end of $total :</i></div>";
        foreach ($results->response->docs as $doc)
        {  
          $id = $doc->id;
          $title = $doc->title;
          $desc = $doc->description;
          if($title=="" ||$title==null)
          {
            $title = $doc->dc_title;
            if($title=="" ||$title==null)
              $title="Not available";
          }
          if($desc=="" ||$desc==null)
            $desc="Not available";
          $id2 = $id;
          $id = str_replace("/Users/anubhavjindal/Downloads/solr-8.0.0/yahoo/","",$id);
          foreach($arrayFromCSV as $row1)
            if($id==$row1[0])
            {
              $url = $row1[1];
              break;
            }
          unset($row1);
          echo "<p>Title : <b><a href='$url' target='_blank'>$title</a></b> </br>
            <span style='font-size: 14px;'> URL :</span> <a href='$url' target='_blank' style='color: #934723; font-size: 14px;'>$url</a> </br>
            <span style='font-size: 14px;'>ID : $id2 </span></br>
            Description : $desc</br>
            </p>";
          array_push($stack,$id2);
        }
      }
    ?>
  </body>
</html>
