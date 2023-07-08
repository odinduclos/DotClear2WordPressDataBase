<?php

// GET COMMENTS
$stmt = $db_old->prepare('SELECT * FROM dc_comment WHERE 1');
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_OBJ);

$comments_count = [];

foreach ($comments as $comment) {
    // APPLY MODIFIERS
    $comment->post_id = $comment->post_id + $modifier_post;
    if (!isset($comments_count[$comment->post_id])) {-
        $comments_count[$comment->post_id] = 1;
    } else {
        $comments_count[$comment->post_id]++;
    }

    // CREATE COMMENTS
    $stmt = $db_new->prepare("INSERT INTO wp_comments(
            comment_ID,
            comment_post_ID,
            comment_author,
            comment_author_email,
            comment_author_url,
            comment_date,
            comment_date_gmt,
            comment_content,
            comment_agent,
            comment_type
        )
        VALUES(
            :comment_ID,
            :comment_post_ID,
            :comment_author,
            :comment_author_email,
            :comment_author_url,
            :comment_date,
            :comment_date_gmt,
            :comment_content,
            '',
            ''
        )
    ");
    $stmt->bindParam(":comment_ID", $comment->comment_id);
    $stmt->bindParam(":comment_post_ID", $comment->post_id);
    $stmt->bindParam(":comment_author", $comment->comment_author);
    $stmt->bindParam(":comment_author_email", $comment->comment_email);
    $stmt->bindParam(":comment_author_url", $comment->comment_site);
    $stmt->bindParam(":comment_date", $comment->comment_dt);
    $stmt->bindParam(":comment_date_gmt", $comment->comment_dt);
    $stmt->bindParam(":comment_content", $comment->comment_content);

    if ($stmt->execute()) {
        echo "Comment $comment->comment_id créé avec succès.<br />";
    }
    // else {
    //     echo "<pre>";
    //     $stmt->debugDumpParams();
    //     print_r($db_new->errorInfo());
    //     die('Le programme a quitté en échec.');
    //     echo "</pre>";
    // }

    // UPDATE POST COUNT
    $stmt = $db_new->prepare("UPDATE wp_posts
    SET comment_count = :comment_count
    WHERE ID = :post_id
    ");

    $count = $comments_count[$comment->post_id];
    $stmt->bindParam(":comment_count", $count);
    $stmt->bindParam(":post_id", $comment->post_id);

    if ($stmt->execute()) {
        echo "Updatet post comment coutn to $count.<br />";
    }
    // else {
    //     echo "<pre>";
    //     $stmt->debugDumpParams();
    //     print_r($db_new->errorInfo());
    //     die('Le programme a quitté en échec.');
    //     echo "</pre>";
    // }
}