<?php

function callAPI($method, $url, $data)
{
    $curl = curl_init();
    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }
    // OPTIONS:
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    // EXECUTE:
    $result = curl_exec($curl);
    if (!$result) {
        die("Connection Failure");
    }
    curl_close($curl);
    return $result;
}

function isValidTimeStamp($timestamp)
{
    return ((string)(int)$timestamp === $timestamp)
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
}

function get_content($URL)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function results($timestamp, $subreddit, $size)
{
    /*

    http://glfinder-api:5000/feeds/all/<subreddit>/<rlimit>
    http://glfinder-api:5000/feeds/all/<subreddit>/<timestamp>/<rlimit>
    http://glfinder-api:5000/feeds/filter/<subreddit>/<word>/

    */
    $baseURL = "http://127.0.0.1:5000/";
    // $data = get_content($baseURL + "feeds/all/$subreddit/$timestamp/$size");
    $data = get_content("http://127.0.0.1:5000/feeds/all/$subreddit/$timestamp/$size");
    return json_decode($data, true);
}

$subreddits_array = ["fashionreps", "repsneakers", "designerreps", "flexicas", "reptime", "repladies"];
$entries_array = ["150", "300", "450"];

if (isset($_GET['search'])) {

    $search_query = filter_input(INPUT_POST, 'search_query', FILTER_SANITIZE_STRING);
    $subreddit = filter_input(INPUT_POST, 'subreddit', FILTER_SANITIZE_STRING);

    if (in_array($subreddit, $subreddits_array)) {
        $data = callAPI('POST', "http://127.0.0.1:5000/feeds/filter/$subreddit", json_encode(array("keyword" => $search_query), JSON_UNESCAPED_SLASHES));
        die($data);
    } else {
        die(json_encode(array("status" => "failed")));
    }
}

if (isset($_GET['subreddit']) && in_array($_GET['subreddit'], $subreddits_array)) {
    $subreddit = $_GET['subreddit'];
    $next_item = "?subreddit=$subreddit&next=";
} else {
    $subreddit = 'fashionreps';
    $next_item = '?next=';
}

if (isset($_GET['next']) && isValidTimeStamp($_GET['next']) == true) {
    $timestamp = $_GET['next'];
} else {
    $timestamp = time();
}

if (isset($_GET['size']) && in_array($_GET['size'], $entries_array)) {
    $size = $_GET['size'];
    $size_get = "&size=$size";
} else {
    $size = 150;
}

if (isset($_GET['w2c_only']) && $_GET['w2c_only'] == 'yes') {
    $w2c_only = 'checked';
    $w2c_uri = '&w2c_only=yes';
} else {
    $w2c_only = 'unchecked';
    $w2c_uri = '&w2c_only=no';
}


?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta subreddit="<?php echo $subreddit; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>GLFinder - Replica Quality Control</title>
    <meta name="title" content="GLFinder - Quality Control your Replicas!">
    <meta name="description" content="Archive of replicas that have gone through the quality control and successfully passed through.">
    <link rel="icon" type="image/png" href="assets/favicon.png" />
    <!-- <link rel="stylesheet" href="assets/css/bootstrap.min.css"> -->
    <script src="https://kit.fontawesome.com/8a64eb2445.js" crossorigin="anonymous"></script>
    <script src="assets/js/lazyload.min.js"></script>

    <!-- Bootstrap v5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <!-- Custom Bootstrap Theming -->
    <link rel="stylesheet" href="assets/css/bootstrap.custom.css">

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-149670194-2"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());

        gtag('config', 'UA-149670194-2');
    </script>

    <style>
        #return-to-top {
            z-index: 1000;
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgb(0, 0, 0);
            background: rgba(0, 0, 0, 0.7);
            width: 50px;
            height: 50px;
            display: block;
            text-decoration: none;
            -webkit-border-radius: 35px;
            -moz-border-radius: 35px;
            border-radius: 35px;
            display: none;
            -webkit-transition: all 0.3s linear;
            -moz-transition: all 0.3s ease;
            -ms-transition: all 0.3s ease;
            -o-transition: all 0.3s ease;
            transition: all 0.3s ease;
        }

        #return-to-top i {
            color: #fff;
            margin: 0;
            position: relative;
            left: 16px;
            top: 13px;
            font-size: 19px;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            -ms-transition: all 0.3s ease;
            -o-transition: all 0.3s ease;
            transition: all 0.3s ease;
        }

        #return-to-top:hover {
            background: rgba(0, 0, 0, 0.9);
        }

        #return-to-top:hover i {
            color: #fff;
            top: 5px;
        }

        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        p.card-text,
        h3.card-text {
            font-size: 18px;
            white-space: nowrap;
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        img {
            opacity: 0;
        }

        img:not(.initial) {
            transition: opacity 1s;
        }

        img.initial,
        img.loaded,
        img.error {
            opacity: 1;
        }

        img:not([src]) {
            visibility: hidden;
        }

        .circle {
            position: absolute;
            height: 32px;
            width: 32px;
            border-radius: 16px;
            background-color: rgb(70, 209, 96);
            margin-top: 20px;
            top: -15px;
            right: 5px;
        }

        .text-circle {
            top: 4px;
            position: relative;
            font-weight: bold;
            color: white;
            text-align: center;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        @media (max-width: 768px) {
            .d-flex {
                display: block !important;
            }
        }

        @media (max-width: 500px) {
            #subreddit {
                float: unset !important;
            }

            #quantity {
                float: unset !important;
            }
        }

        /* Footer */
        .Footer {
            display: flex;
            flex-direction: column;
            width: 100%;
            margin-top: 2rem;
            align-content: center;
            align-items: center;
            padding: 2rem 5rem;
        }

        .Footer .Socials {
            padding: 1rem 0;
            display: flex;
            flex-direction: row;
            width: 80%;
            justify-content: center;
        }

        .social-icon,
        .Socials {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-content: center;
        }

        .Socials {
            flex-direction: column;
            font-size: 2rem;
            width: 10vw;
            color: #9c27b0;
            color: var(--primary);
        }

        .Socials p {
            width: 100%;
        }

        .Socials a {
            text-decoration: none !important;
        }

        .Footer .Socials .social-icon {
            padding: 0 1.5rem;
        }

        #footer-links a {
            color: unset;
            text-decoration: none !important;
        }
    </style>
    <link href="assets/album/css/album.css" rel="stylesheet">
</head>

<body is_w2c_only="<?php if (isset($_GET['w2c_only']) && $_GET['w2c_only'] === 'yes') : ?>yes<?php else : ?>no<?php endif; ?>">
    <a href="javascript:" id="return-to-top"><i class="fas fa-chevron-up"></i></a>
    <!-- Header old -->
    <!-- <header>
    <div class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container d-flex justify-content-between">
            <a href="/" class="navbar-brand d-flex align-items-center">
                <svg xmlns="https://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor"
                     stroke-linecap="round" stroke-linejoin="round" stroke-width="2" aria-hidden="true"
                     class="mt-1 mr-2"
                     viewBox="0 0 24 24" focusable="false">
                    <image href="assets/favicon.png" width="20" height="20"/>
                </svg>
                <strong>GL Finder</strong>
            </a>
        </div>
    </div>
</header> -->
    <!-- Header new -->
    <header class="p-3 border-bottom sticky-top bg-body" id="header-nav">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                <a href="/" class="d-flex align-items-center mb-2 mb-lg-0 text-dark text-decoration-none">
                    <svg xmlns="https://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" aria-hidden="true" class="mt-1 mr-2" viewBox="0 0 24 24" focusable="false">
                        <image href="https://glfinder.com/assets/favicon.png" width="20" height="20"></image>
                    </svg>
                    <strong class="ps-1">GL Finder</strong>
                </a>

                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                    <!--<li><a href="#" class="nav-link px-2 link-secondary">Overview</a></li> -->
                </ul>

                <form class="d-flex" action="javascript:void(0);">
                    <!-- Search -->
                    <input class="form-control me-2" type="text" placeholder="Filter List" aria-label="Search" name="filterword" id="filterword" <?php $filter_value; ?>>
                    <input type="hidden" name="w2c_only" id="w2c_only_input" value="<?php if (isset($_GET['w2c_only']) && $_GET['w2c_only'] === 'yes') : ?>yes<?php else : ?>no<?php endif; ?>">
                    <!-- <button class="btn btn-outline-secondary" type="submit">Search</button> -->
                    <!-- Subreddit -->
                    <select class="form-select me-2" aria-label="Default select example" name="subreddit" id="subreddit">
                        <option value='' <?php if (!isset($subreddit)) {
                                                echo "selected";
                                            } ?> disabled>Pick a subreddit
                        </option>
                        <?php foreach ($subreddits_array as $key => $value) {
                            if ($subreddit == $value) {
                                $selected = "selected";
                            }
                            echo "<option value='$value' $selected>r/$value</option>";
                            unset($selected);
                        } ?>
                    </select>

                    <!-- Quantity -->
                    <select class="form-select me-2" aria-label="Default select example" name="quantity" id="quantity">
                        <?php foreach ($entries_array as $key => $entries) {
                            if ($size == $entries) {
                                $selected = "selected";
                            }
                            echo "<option value='$entries' $selected>$entries Entries</option>";
                            unset($selected);
                        } ?>
                    </select>

                    <!-- W2C -->
                    <input type="checkbox" class="btn-check" id="w2c_only" <?php echo $w2c_only; ?> autocomplete="off">
                    <label class="btn btn-primary" for="w2c_only">W2C</label>

                    <!--                     <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="w2c_only" <?php echo $w2c_only; ?>>
                        <label class="form-check-label" for="w2c_only">W2C Only</label>
                    </div> -->
                </form>

            </div>
        </div>
    </header>
    <!-- #Header new -->

    <!-- Hero -->
    <div class="px-4 py-5 text-center" style="background-image: url();background-size: cover; background-position:center;">
        <img class="d-block mx-auto mb-4" src="https://glfinder.com/assets/favicon.png" alt="" width="75" height="75">
        <h1 class="display-5 fw-bold">GL Finder</h1>
        <div class="col-lg-6 mx-auto">
            <h2 class="lead mb-4">
                Voluptas repudiandae odit iure quia blanditiis. Illo error nihil veritatis assumenda. Est et saepe
                corrupti possimus id quis quo. Inventore et et animi sunt odio voluptatem. Non laudantium quia ipsum
                blanditiis repudiandae explicabo incidunt eveniet.
            </h2>
            <!--             <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <button type="button" class="btn btn-primary btn-lg px-4 gap-3">Primary button</button>
                <button type="button" class="btn btn-outline-secondary btn-lg px-4">Secondary</button>
            </div> -->
        </div>
    </div>
    <!-- #Hero -->



    <main role="main">

        <div class="album py-3 bg-light">
            <div class="container">

                <!-- Old filter bar
                <div class="pb-3">
                                         <label for="filterword">Filter List</label>
                    <input type="text" name="filterword" id="filterword" <#?php $filter_value; ?> > -->
                <!--                     <select class="custom-select custom-select-sm w-auto float-right" name="subreddit" id="subreddit" style="vertical-align: unset;">
                        <option value='' <#?php if (!isset($subreddit)) {
                                                echo "selected";
                                            } ?> disabled>Pick a subreddit
                        </option>
                        <#?php foreach ($subreddits_array as $key => $value) {
                            if ($subreddit == $value) {
                                $selected = "selected";
                            }
                            echo "<option value='$value' $selected>r/$value</option>";
                            unset($selected);
                        } ?>
                    </select>
                
                    <select class="custom-select custom-select-sm w-auto float-right" name="quantity" id="quantity" style="vertical-align: unset;">
                        <#?php foreach ($entries_array as $key => $entries) {
                            if ($size == $entries) {
                                $selected = "selected";
                            }
                            echo "<option value='$entries' $selected>$entries Entries</option>";
                            unset($selected);
                        } ?>
                    </select>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="w2c_only" <#?php echo $w2c_only; ?>>
                        <label class="form-check-label" for="w2c_only">W2C Only</label>
                    </div>
                </div>
                    -->

                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4" id="main_holder">
                    <?php
                    $results_data = results($timestamp, $subreddit, $size);
                    foreach ($results_data['assets'] as $datum) {

                        /* print'<pre>';
                        print_r($datum);
                        print'</pre>'; */
                        if (isset($datum['imgur_iframe'])) {
                            $imgur_preview = $datum['imgur_iframe'];
                        }

                        if ($datum['w2c_link'] != null) {
                            $w = "$datum[w2c_link]";
                            //echo $w;
                            //echo 'WWW';
                            $p = "/<\//";
                            //$w2c = preg_replace('&lt;/a&gt;&lt;/p&gt;', '', $w);
                            $w2c = str_ireplace(array('&lt;/a&gt;', '&lt;/p&gt;'), '', $w);
                            //echo $w2c; 
                        }

                        if ($datum['thumbnail_link'] != null) {
                            $image = "$datum[thumbnail_link]";
                            // ;                              echo $image;
                            // $imgHeaders = @get_headers(str_replace(" ", "%20", $image))[0];
                            // if ($imgHeaders == 'HTTP/1.1 404 Not Found') {
                            //     $image = NULL;

                            // }
                        } else {
                            // echo 'here'; 
                            $image = NULL;
                            // $i =  "https://www.reddit.com/$datum[reddit_link].json";
                            // // echo $x;
                            // // $u  = 'https://www.reddit.com/r/FashionReps/comments/sd4u1o/qc_lv_belt_from_bs_v2_wouldnt_let_me_put_the_pics/.json';
                            // $curl = curl_init();
                            // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            // curl_setopt($curl, CURLOPT_URL, $i);
                            // $res = curl_exec($curl);
                            // curl_close($curl);
                            // $x = json_decode($res, True)[0]['data']['children'][0]['data']['thumbnail'];            
                            // echo 'x value here \n';
                            // echo $x;
                            //$t = json_de  code($res,True)[1]['data']['children'][2]['data']['replies']['data']['children'][0]['data']['body'];
                            //$word = 'https://item.taobao.com';
                            //if (strpos($t, $word) === FALSE) {

                            //}else{
                            //   $w2c = $t;
                            //}

                            // if (strlen($x) < 20) {
                            //     //echo 'inside if';
                            //     $image = NULL;
                            // } else {
                            //     // echo 'in else statement';
                            //     $image = $x;
                            // }
                        }


                        //                    if ($datum['w2c_link'] != null) {
                        //                       $w2c = "$datum[w2c_link]";
                        //                    }

                        if ($datum['gl_counter'] !== 1) {
                            $gl_counter = $datum['gl_counter'];
                        }
                        if ($datum['rl_counter'] !== 1) {
                            $rl_counter = $datum['rl_counter'];
                        }

                        if (!isset($gl_counter) || $gl_counter <= 0) {
                            $gl_counter = 0;
                        }
                        if (!isset($rl_counter) || $rl_counter <= 0) {
                            $rl_counter = 0;
                        }

                        /* Button color */

                        if ($gl_counter > $rl_counter) {
                            $btn_color = 'btn-success';
                        } else if ($rl_counter > $gl_counter) {
                            $btn_color = 'btn-primary';
                        } else if ($gl_counter == $rl_counter) {
                            $btn_color = 'btn-secondary';
                        }

                        //if ($image!=null){
                        //    $imgHeaders = @get_headers( str_replace(" ", "%20", $image) )[0];
                        //    if( $imgHeaders == 'HTTP/1.1 404 Not Found' ) {
                        //        //img doesn't exist
                        //        $image  = null;
                        //    }
                        //}
                    ?>
                        <div class="item col mb-4 <?php if (!isset($imgur_preview) && !isset($w2c)) {
                                                        echo 'no-button"';
                                                    } else {
                                                        echo '"';
                                                    };
                                                    echo "data-id='$datum[reddit_link_id]'" ?>>
                            <!-- Card -->
                            <div class=" card mb-4 shadow-sm h-100">
                            <a href="<?php echo "https://www.reddit.com$datum[reddit_link]"; ?>" target='_blank' rel='noreferrer'>
                                <?php if (!isset($image)) { ?>
                                    <svg class="bd-placeholder-img card-img-top" width="100%" height="225" xmlns="https://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false" role="img" aria-label="Thumbnail">
                                        <title><?php echo "$datum[reddit_title]"; ?></title>
                                        <rect width="100%" height="100%" fill="#55595c" />
                                        <text x="50%" y="50%" fill="#eceeef" dy=".3em">Thumbnail not available</text>
                                    </svg>
                                <?php } else { ?>
                                    <img class="bd-placeholder-img card-img-top lazy" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 3 2'%3E%3C/svg%3E" data-src="<?php echo "$image"; ?>" alt="<?php echo "$datum[reddit_title]"; ?>" title="<?php echo "$datum[reddit_title]"; ?>" height="225" width="100%" style="object-fit: cover;object-position: 50% 50%" />
                                <?php } ?>
                            </a>

                            <div class="card-body d-flex flex-column h-100" style="justify-content: space-between;">
                                <!-- Title -->
                                <h3 class="card-text"><?php echo "$datum[reddit_title]"; ?></h3>

                                <div class="justify-content-between align-items-center">

                                    <!-- Buttons -->
                                    <div>
                                        <div class="align-self-end btn-grouped">
                                            <?php if (isset($imgur_preview)) { ?>
                                                <a href="<?php echo $imgur_preview; ?>" class="btn btn-sm btn-primary mb-2" target='_blank' style="width:100%" rel='noreferrer'>Imgur <i class="bi bi-card-image"></i></a>
                                            <?php }
                                            if (isset($w2c)) { ?>
                                                <a href="<?php echo $w2c; ?>" class="btn btn-sm <?php echo $btn_color ?>" target='_blank' style="width:100%" rel='noreferrer'>W2C <i class="bi bi-link-45deg"></i></a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <!-- #Buttons -->

                                </div>
                            </div>
                            <div class="card-footer" style="display:flex;flex-direction:row;justify-content: space-between;">
                                <div>
                                    <!-- Report -->
                                    <a data-bs-toggle="tooltip" title="Report on Reddit" href="<?php echo "https://www.reddit.com$datum[reddit_link]"; ?>" target='_blank' rel='noreferrer'>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-flag-fill me-2" viewBox="0 0 16 16">
                                            <path d="M14.778.085A.5.5 0 0 1 15 .5V8a.5.5 0 0 1-.314.464L14.5 8l.186.464-.003.001-.006.003-.023.009a12.435 12.435 0 0 1-.397.15c-.264.095-.631.223-1.047.35-.816.252-1.879.523-2.71.523-.847 0-1.548-.28-2.158-.525l-.028-.01C7.68 8.71 7.14 8.5 6.5 8.5c-.7 0-1.638.23-2.437.477A19.626 19.626 0 0 0 3 9.342V15.5a.5.5 0 0 1-1 0V.5a.5.5 0 0 1 1 0v.282c.226-.079.496-.17.79-.26C4.606.272 5.67 0 6.5 0c.84 0 1.524.277 2.121.519l.043.018C9.286.788 9.828 1 10.5 1c.7 0 1.638-.23 2.437-.477a19.587 19.587 0 0 0 1.349-.476l.019-.007.004-.002h.001" />
                                        </svg></a>
                                    <!-- Date -->
                                    <small class="text-muted post_date" <?php
                                                                        $date = $datum['reddit_created_utc'];
                                                                        echo "data-timestamp='$date'";
                                                                        unset($date);
                                                                        ?>>
                                    </small>
                                </div>
                                <!-- Vote -->
                                <div>
                                    <span style="color:var(--success);white-space: nowrap;"><i class="bi bi-arrow-up-short"></i><?php echo $gl_counter; ?> GL</span> <span style="color:var(--primary);white-space: nowrap;"><i class="bi bi-arrow-down-short"></i><?php echo $rl_counter; ?> RL</span>
                                </div>
                            </div>
                            <!-- Counter GL -->
                            <!-- <#?php if (isset($gl_counter)) { ?>
                                <div class="circle">
                                    <p class="text-circle"><#?php echo $gl_counter; ?></p>
                                </div>
                            <#?php } ?> -->
                        </div>
                        <!-- #Card -->
                </div>
            <?php
                        unset($titulo, $image, $imgur_preview, $w2c, $gl_counter, $rl_counter);
                        $last_timestamp = $datum['reddit_created_utc'];
                    }
                    unset($datum);

            ?>

            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4" id="search_holder" hidden>

            </div>
        </div>
        </div>

    </main>

    <footer class="text-muted">
        <!-- Next Button -->
        <div class="container">
            <a id="next_page" class="btn btn-outline-primary" href="<?php echo $next_item . $last_timestamp . $size_get . $w2c_uri ?>" data-href="<?php echo $next_item . $last_timestamp . $size_get . $w2c_uri ?>">Next page <i class="bi bi-chevron-double-right"></i></a>
        </div>

        <!-- Footer -->
        <div class="Footer">
            <div class="Socials"><a href="/discord"><i class="fab fa-discord social-icon" aria-hidden="true"></i></a><a href="https://reddit.com/r/reparchive" rel="author noopener noreferrer nofollow"><i class="fab fa-reddit social-icon" aria-hidden="true"></i></a><a href="/youtube" rel="author noopener noreferrer nofollow"><i class="fab fa-youtube social-icon" aria-hidden="true"></i></a><a href="https://twitter.com/reparchive" rel="author noopener noreferrer nofollow"><i class="fab fa-twitter social-icon" aria-hidden="true"></i></a><a href="/account" rel="author noopener noreferrer nofollow"><i class="fas fa-sign-in-alt social-icon" aria-hidden="true"></i></a></div>
            <p style="text-align: left; font-size: 0.9em;">
            <p>Disclaimer: This is a live feed pulled from external sources, we cannot take responsibility for any items listed here. Please contact the sellers or marketplace provider for complaints. No warranties for correctness of information. CH Web Development is not affiliated with any items or brands shown. </p>
            <p id="footer-links"><a href="https://ch-webdev.com" rel="author noopener noreferrer" target="_blank" title="CH Web Development Homepage">Developed by CH Web Development</a> &copy; 2022 | <a href="https://ch-webdev.com/contact" rel="author noopener noreferrer nofollow" target="_blank">Contact</a> | <a href="https://ch-webdev.com/impressum/" rel="author noopener noreferrer nofollow" target="_blank" title="Impressum">Impressum</a> | <a href="https://ch-webdev.com/privacy-policy/" rel="author noopener noreferrer nofollow" target="_blank">Privacy</a></p>
        </div>
    </footer>

    <script src="assets/js/jquery-3.5.1.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/moment-with-locales.min.js"></script>

    <script>
        $(document).ready(function() {

            function tooltip() {
                $('.circle').tooltip({
                    trigger: "hover",
                    placement: "top",
                    title: "Total GL's"
                })
            }

            $('[data-toggle="tooltip"]').tooltip();

            $(window).scroll(function() {
                if ($(this).scrollTop() >= 50) {
                    $('#return-to-top').fadeIn(200);
                } else {
                    $('#return-to-top').fadeOut(200);
                }
            });
            $('#return-to-top').click(function() {
                $('body,html').animate({
                    scrollTop: 0
                }, 500);
            });

            var lazyLoadInstance = new LazyLoad({
                elements_selector: ".lazy",
                use_native: true
            });

            //Filter Vars
            var typingTimer;
            var doneTypingInterval = 1000;
            var $search = $('#filterword');

            function convert_dates() {

                var locale = window.navigator.userLanguage || window.navigator.language;
                moment.locale(locale);

                var localeData = moment.localeData();
                var format = localeData.longDateFormat('L');

                $('small.post_date').each(function() {
                    var date = $(this).data('timestamp');
                    var m1 = moment(moment.unix(date), format);
                    $(this).text(m1.format(format));
                })
            }

            function getUrlVars() {
                var vars = [],
                    hash;
                var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
                for (var i = 0; i < hashes.length; i++) {
                    hash = hashes[i].split('=');
                    vars.push(hash[0]);
                    vars[hash[0]] = hash[1];
                }
                return vars;
            }

            function removeURLParameter(url, parameter) {
                //prefer to use l.search if you have a location/link object
                var urlparts = url.split('?');
                if (urlparts.length >= 2) {

                    var prefix = encodeURIComponent(parameter) + '=';
                    var pars = urlparts[1].split(/[&;]/g);

                    //reverse iteration as may be destructive
                    for (var i = pars.length; i-- > 0;) {
                        //idiom for string.startsWith
                        if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                            pars.splice(i, 1);
                        }
                    }

                    url = urlparts[0] + '?' + pars.join('&');
                    return url;
                } else {
                    return url;
                }
            }

            function w2c_only() {
                var base_url = $('#next_page').data('href') + '&w2c_only=';
                let is_w2c_only = 'no';
                if ($('#w2c_only').is(":checked")) {
                    is_w2c_only = 'yes';
                    base_url += is_w2c_only;
                    $(".no-button").hide();
                    // if .hide() doesn't work try the second one 
                    $(".no-button").css('display', 'none');
                } else {
                    base_url += is_w2c_only;
                    $(".no-button").show();
                }
                $('body').attr('is_w2c_only', is_w2c_only);
                $("#w2c_only_input").val(is_w2c_only);
                // alert(is_w2c_only)
                $('#next_page').attr('href', base_url);
            }

            function doneTyping() {
                var search = $search.val();
                if (search != "") {
                    $.ajax({
                        url: '?search',
                        type: 'post',
                        data: {
                            search_query: search,
                            subreddit: $('meta[subreddit]').attr('subreddit')
                        },
                        dataType: 'json',
                        success: function(response) {

                            var len = response['assets'].length;
                            $('#main_holder').prop('hidden', true);
                            $("#search_holder").empty();

                            for (var i = 0; i < len; i++) {

                                var imgur_iframe = response['assets'][i]['imgur_iframe'];
                                var reddit_created_utc = response['assets'][i]['reddit_created_utc'];
                                var reddit_link = response['assets'][i]['reddit_link'];
                                var reddit_link_id = response['assets'][i]['reddit_link_id'];
                                var reddit_title = response['assets'][i]['reddit_title'];
                                var thumbnail_link = response['assets'][i]['thumbnail_link'];
                                var w2c_link = response['assets'][i]['w2c_link'];
                                var gl_counter = response['assets'][i]['gl_counter'];

                                var thumbnail_element = '';
                                var imgur_element = '';
                                var w2c_element = '';
                                var gl_element = '';

                                if (thumbnail_link != null && thumbnail_link.length > 1) {
                                    thumbnail_element = '<img class="bd-placeholder-img card-img-top lazy" src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 3 2\'%3E%3C/svg%3E" data-src="' + thumbnail_link + '" alt="' + reddit_title + '" title="' + reddit_title + '" height="225" width="100%" style="object-fit: cover;object-position: 50% 50%"/>';
                                } else {
                                    thumbnail_element = '<svg class="bd-placeholder-img card-img-top" width="100%" height="225" xmlns="https://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false" role="img" aria-label="Thumbnail"><title>' + reddit_title + '</title><rect width="100%" height="100%" fill="#55595c"/><text x="50%" y="50%" fill="#eceeef" dy=".3em">Thumbnail not available</text></svg>';
                                }

                                if (imgur_iframe != null) {
                                    imgur_element = '<a href="' + imgur_iframe + '" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noreferrer">Imgur</a>';
                                }

                                if (w2c_link != null) {
                                    w2c_element = '<a href="' + w2c_link + '" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noreferrer">W2C</a>';
                                }

                                if (gl_counter !== 1) {
                                    gl_element = '<div class="circle"><p class="text-circle">' + gl_counter + '</p></div>';
                                }

                                var reddit_card = '<div class="item col" data-id="' + reddit_link_id + '">' +
                                    '<div class="card mb-4 shadow-sm">' +
                                    '<a href="https://www.reddit.com' + reddit_link + '" target="_blank" rel="noreferrer">' +
                                    thumbnail_element +
                                    '</a>' +
                                    '<div class="card-body flex-column h-100">' +
                                    '<p class="card-text">' + reddit_title + '</p>' +
                                    '<div class="justify-content-between align-items-center">' +
                                    '<small class="text-muted post_date" data-timestamp="' + reddit_created_utc + '"></small>&nbsp;' +
                                    '<div class="btn-group float-right">' +
                                    imgur_element +
                                    w2c_element +
                                    '</div>' +
                                    '</div>' +
                                    '</div>' +
                                    gl_element +
                                    '</div>' +
                                    '</div>';

                                $("#search_holder").append(reddit_card);
                                tooltip();
                            }
                            // alert('hehe');
                            convert_dates();
                            lazyLoadInstance.update();
                            $("#search_holder").prop('hidden', false);
                            $('#next_page').prop('hidden', true);
                        }
                    });

                } else {
                    $("#search_holder").prop('hidden', true);
                    $('#next_page').prop('hidden', false);
                    $('#main_holder').prop('hidden', false);
                }
            }

            tooltip();

            convert_dates();

            w2c_only();

            $search.on('keyup', function() {
                clearTimeout(typingTimer);
                // alert($search.val());
                typingTimer = setTimeout(doneTyping, doneTypingInterval);
            });

            $search.on('keydown', function() {
                clearTimeout(typingTimer);
            });


            $('#subreddit').on('change', function() {
                var subreddit = $(this).val();
                var operator = '';

                if (window.location.href in getUrlVars()) {
                    operator = '?';
                } else {
                    operator = '&';
                }

                var next_url = removeURLParameter(window.location.href, 'subreddit');

                if (subreddit) {
                    let w2c_only = '&w2c_only=' + $("body").attr('is_w2c_only');
                    var url = next_url + operator;
                    window.location = url + "&subreddit=" + subreddit + w2c_only;
                }

                return false;
            });

            $('#quantity').on('change', function() {
                var quantity = $(this).val();
                var operator = '';

                if (window.location.href in getUrlVars()) {
                    operator = '?';
                } else {
                    operator = '&';
                }

                var next_url = removeURLParameter(window.location.href, 'size');

                if (quantity) {
                    var url = next_url + operator;
                    window.location = url + "size=" + quantity;
                }

                return false;
            });

            $('#w2c_only').on('change', function() {
                w2c_only();
            });

            $('#top').on('click', function(e) {
                e.preventDefault();
            })

        });

        /* Tooltips */
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
    <?php if (isset($_GET['w2c_only']) && $_GET['w2c_only'] === 'yes') : ?>
        <script>
            w2c_only();
        </script>
    <?php endif; ?>
</body>

</html>