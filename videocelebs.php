<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);


include '../config.php';
include '../functions.php';
include '../libs/simple_html_dom.php';
$mysqli->set_charset('utf8mb4');

$site_url = 'https://babepedia.com/';




$headers = [
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:135.0) Gecko/20100101 Firefox/135.0",
    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
    "Accept-Language: en-US,en;q=0.5",
  //  "Accept-Encoding: gzip, deflate, br, zstd",
    "Connection: keep-alive",
    "Cookie: PHPSESSID=fd6b8f7cbaec82ae125df78358d73ede; kt_referer=https%3A%2F%2Fwww.google.com%2F; kt_ips=118.179.44.145; kt_tcookie=1",
    "Upgrade-Insecure-Requests: 1",
    "Sec-Fetch-Dest: document",
    "Sec-Fetch-Mode: navigate",
    "Sec-Fetch-Site: none",
    "Sec-Fetch-User: ?1",
    "Priority: u=0, i",
];

// all posts
if(isset($_REQUEST['newposts']))
{

    $postslugs = [];

    $lastpage = $_REQUEST['newposts'];
    for ($i = $lastpage;$i >0;$i--)
    {
        file_put_contents('vidslb.txt', $i);
        $url = "https://videocelebs.net/page/{$i}";
        echo $url;
        $resp = func_get_content($url,'get',[],$headers);

      //  echo $resp;
       // exit();
        $html = str_get_html($resp);

        $cards = $html->find("div.item div.first a");
        foreach($cards as $card)
        {
            $href = $card->href;
            $slug = basename($href,'.html');
            $ima = $card->find("img");
            $image = $ima[0]->src;
            $title = $ima[0]->alt;

            $slugx = $mysqli->real_escape_string($slug);
            $titlex = $mysqli->real_escape_string($title);
            $imagex = $mysqli->real_escape_string($image);
            if(in_array($slug,$postslugs))
            {

            }else{

                $postslugs[] = $slug;
                $xq = $mysqli->query("SELECT * FROM `videocelebs_posts` WHERE slug = '{$slugx}' limit 1");
                if($xq->num_rows < 1)
                {

                    $mysqli->query("INSERT INTO `videocelebs_posts`( `slug`, `title`, `poster`) VALUES ('{$slugx}','{$titlex}','{$imagex}')");
                }
            }

        }

// exit();
    }

}

if(isset($_REQUEST['pdata']))
{
    $xq = $mysqli->query("SELECT * FROM `videocelebs_posts` WHERE stat =0 order by id desc limit 1000");
    while ($row = $xq->fetch_assoc())
    {
        $slug = $row['slug'];
        $url = "https://videocelebs.net/{$slug}.html";
        echo $url;
        $reps = func_get_content($url,'get',[],$headers);
        $html = str_get_html($reps);
        $pagedata = [];
        $images = [];
        $embed_id = 0;
        if (1)
        {
            $metas = $html->find('meta');
            foreach ($metas as $meta) {
                if (isset($meta->attr['property'])) {
                    if ($meta->attr['property'] == 'og:image') {
                        $pagedata['thumb'] = $meta->attr['content'];
                    }elseif ($meta->attr['property'] == 'og:description') {
                        $pagedata['description'] = $meta->attr['content'];
                    }
                }
            }
        }

        $body = $html->find("div.singl",0);
        if($body)
        {
            $ptrn = '#videocelebs.net\/embed\/(\d+)#';
            if(preg_match($ptrn,$html,$matches))
            {
                $embed_id = $matches[1];
            }

            preg_match("/videoId:\s*'(\d+)'/", $html, $matches);

            if (!empty($matches[1])) {
                $embed_id = $matches[1];
            }


            $imgs = $body->find("p span a.item");
            foreach($imgs as $imgu)
            {
                $img = $imgu->find("img",0);
                if($img)
                {
                    $images[] = [$img->src,$imgu->href];
                }
            }

            $pagedata['images'] = $images;
            $pagedata['embed_id'] = $embed_id;

            $tgars = $body->find("div.entry-utility a");
            if($tgars)
            {
                foreach ($tgars as $tgar) {
                    $ahrf = $tgar->href;
                    $slug = basename($ahrf);

                    $name = trim($tgar->plaintext);
                    if(strpos($ahrf,'/actress/')>0)
                    {
                        $pagedata['actress'] = [$slug,$name];
                    }elseif (strpos($ahrf,'/tvshow/')>0)
                    {
                        $pagedata['tvshow'] = [$slug,$name];
                    }elseif (strpos($ahrf,'/tag/')>0)
                    {
                        $pagedata['tag'] = [$slug,$name];
                    }else{
                        $ptrrn = '#videocelebs.net\/(\w+)\/(\w+)#';
                        if (preg_match($ptrrn, $ahrf, $matches))
                        {
                            $pagedata[$matches[1]] = [$slug,$name];
                        }
                    }
                }
            }

            $targetStyle = "font-size:16px; line-height:18px; margin:7px 0 10px 0; float:left;";

            foreach ($body->find('div[style]') as $div) {
                if (trim($div->style) === $targetStyle) {
                   $pagedata['description'] = trim($div->innertext);
                }
            }
        }

        $pagedatax = $mysqli->real_escape_string(json_encode($pagedata));

        $mysqli->query("UPDATE `videocelebs_posts` SET `pagedata`='{$pagedatax}',`stat` = '1' WHERE id = '{$row['id']}' limit 1");
    }

}