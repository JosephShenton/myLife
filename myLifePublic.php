<?php 
  require "vendor/autoload.php";

  use Abraham\TwitterOAuth\TwitterOAuth;

  $config = [
    "work_or_school" => "school", // Possible: school or work or custom emoji
    "work_school_start" => 9, // 24 hour time
    "work_school_finish" => 15, // 24 hour time
    "bed_time" => 22, // 24 hour time
    "wake_up_time" => 6, // 24 hour time
    "twitter_username" => "username",
    "timezone" => "Country/City", // Look this up here http://php.net/manual/en/timezones.php mine is Australia/Sydney
  ];

  date_default_timezone_set($config['timezone']);

  $connection = new TwitterOAuth(CONSUMER_TOKEN, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_SECRET);
  $content = $connection->get("account/verify_credentials");

  
  try {
      $welcomeMessage = $connection->get("direct_messages/sent", ["count" => "2"]);
      $dms = json_decode(json_encode($welcomeMessage), true);
      $most_recent = $dms[0]['created_at'];
      $tweets = json_decode(json_encode($connection->get("statuses/user_timeline", ["count" => 2])), true);
      $tweets_most = $tweets[0]['created_at'];

      $likes = json_decode(json_encode($connection->get("favorites/list", ["count" => 200, "screen_name" => $config['twitter_username']])), true);
      $most_recent_likes = array();
      foreach ($likes as $like) {
        $most_recent_likes[] = $like['id'];
      }
      $most_recent_likes = json_encode($most_recent_likes, JSON_PRETTY_PRINT);

      if (!file_get_contents('like-'.$config['twitter_username'].'.json')) {
        file_put_contents('like-'.$config['twitter_username'].'.json', $most_recent_likes);
        $like_offline = true;
        // echo "Offline | Likes";
      } else {
        if (file_get_contents('like-'.$config['twitter_username'].'.json') != $most_recent_likes) {
          file_put_contents('like-'.$config['twitter_username'].'.json', $most_recent_likes);
          $like_offline = false;
          // echo "Online | Likes";
        } else {
          $like_offline = true;
          // echo "Offline | Likes";
        }
      }

      $tweets_most = $tweets[0]['created_at'];
      if (strtotime($tweets_most) <= strtotime('-1 hours') || strtotime($tweets_most) <= strtotime('-1 hour') || strtotime($tweets_most) <= strtotime('-10 minutes') || strtotime($tweets_most) <= strtotime('-10 minute') || strtotime($tweets_most) <= strtotime('-5 minutes') || strtotime($tweets_most) <= strtotime('-5 minute')) {
          // echo "Offline | Tweets";
          $tweets_offline = true;
      } else {
          // echo "Online | Tweets";
          $tweets_offline = false;
      }
      if (strtotime($most_recent) <= strtotime('-1 hours') || strtotime($most_recent) <= strtotime('-1 hour') || strtotime($most_recent) <= strtotime('-10 minutes') || strtotime($most_recent) <= strtotime('-10 minute') || strtotime($most_recent) <= strtotime('-5 minutes') || strtotime($most_recent) <= strtotime('-5 minute')) {
          // echo "Offline | DMs";
          $dms_offline = true;
      } else {
          // echo "Online | DMs";
          $dms_offline = false;
      }

      if ($dms_offline && $tweets_offline && $like_offline) {
        if (date('H') >= $config['bed_time'] && date('H') > $config['wake_up_time'] - 1) {
           echo "Status: 🛌 (via myLife)\n";
           $connection->post("account/update_profile", ["location" => "Status: 🛌 (via myLife)"]);
        } elseif (date('H') >= $config['work_school_finish']) {
          echo "Status: ❌ (via myLife)\n";
          $connection->post("account/update_profile", ["location" => "Status: ❌ (via myLife)"]);
        } elseif (date('H') >= $config['work_school_start'] && date('H') < $config['work_school_finish'] + 1) {
          if (strtolower($config['work_or_school']) == "school") {
            echo "Status: 🏫 (via myLife)\n";
            $connection->post("account/update_profile", ["location" => "Status: 🏫 (via myLife)"]);
          } elseif (strtolower($config['work_or_school']) == "work") {
            echo "Status: 💼 (via myLife)\n";
            $connection->post("account/update_profile", ["location" => "Status: 💼 (via myLife)"]);
          } else {
            echo "Status: ".$config['work_or_school']." (via myLife)\n";
            $connection->post("account/update_profile", ["location" => "Status: ".$config['work_or_school']." (via myLife)"]);
          }
        } else {
          echo "Status: ❌ (via myLife)\n";
          $connection->post("account/update_profile", ["location" => "Status: ❌ (via myLife)"]);
        }
      } elseif (!$dms_offline || !$tweets_offline || !$like_offline) {
        if (date('H') >= $config['bed_time'] && date('H') > $config['wake_up_time'] - 1) {
           echo "Status: 🛌 but 🔰 (via myLife)\n";
           $connection->post("account/update_profile", ["location" => "Status: 🛌 but 🔰 (via myLife)"]);
        } elseif (date('H') >= $config['work_school_finish']) {
          echo "Status: 🔰 (via myLife)\n";
          $connection->post("account/update_profile", ["location" => "Status: 🔰 (via myLife)"]);
        } elseif (date('H') >= $config['work_school_start'] && date('H') < $config['work_school_finish'] + 1) {
          if (strtolower($config['work_or_school']) == "school") {
            echo "Status: 🏫 but 🔰 (via myLife)\n";
            $connection->post("account/update_profile", ["location" => "Status: 🏫 (via myLife)"]);
          } elseif (strtolower($config['work_or_school']) == "work") {
            echo "Status: 💼 but 🔰 (via myLife)\n";
            $connection->post("account/update_profile", ["location" => "Status: 💼 (via myLife)"]);
          } else {
            echo "Status: ".$config['work_or_school']." but 🔰 (via myLife)\n";
            $connection->post("account/update_profile", ["location" => "Status: ".$config['work_or_school']." (via myLife)"]);
          }
        } else {
          echo "Status: 🔰 (via myLife)\n";
          $connection->post("account/update_profile", ["location" => "Status: 🔰 (via myLife)"]);
        }
      } else {
        echo "Status: ⚠️ (via myLife)\n";
        $connection->post("account/update_profile", ["location" => "Status: ⚠️ (via myLife)"]);
      }
  } catch(Exception $e) {
      echo "Status: ⚠️ (via myLife)\n";
      $connection->post("account/update_profile", ["location" => "Status: ⚠️ (via myLife)"]);
  }
?>
