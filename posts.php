<?php

// GET POSTS
$stmt = $db_old->prepare('SELECT * FROM dc_post WHERE 1');
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_OBJ);

foreach ($posts as $post) {
    // APPLY MODIFIERS
    $post->post_id = $post->post_id + $modifier_post;
    $post->cat_id = $post->cat_id + $modifier_category;
    $post->post_content_xhtml = str_replace('/blog/images/', '/blog/wp-content/uploads/o/', $post->post_content_xhtml);
    $post->post_content_xhtml = str_replace('/blog/public/images/', '/blog/wp-content/uploads/o/', $post->post_content_xhtml);
    $post->post_content_xhtml = str_replace('/blog/public/', '/blog/wp-content/uploads/o/', $post->post_content_xhtml);
    $post->post_content_xhtml = str_replace('/blog/public/', '/blog/wp-content/uploads/o/', $post->post_content_xhtml);
    $post->post_content_xhtml = preg_replace('/\/blog\/index\.php\?post\/[^"]+/', '/blog/' . $post->post_id, $post->post_content_xhtml);
    $post->post_content_xhtml = str_replace('/blog/index.php?', '/blog/', $post->post_content_xhtml);
    $url = $post->post_title;

    // CREATE POSTS
    $stmt = $db_new->prepare("INSERT INTO wp_posts(
            id,
            post_author,
            post_date,
            post_date_gmt,
            post_content,
            post_title,
            post_modified,
            post_modified_gmt,
            post_excerpt, 
            to_ping, 
            pinged, 
            post_content_filtered,
            post_name
        )
        VALUES(
            :id,
            :post_author,
            :post_date,
            :post_date_gmt,
            :post_content,
            :post_title,
            :post_modified,
            :post_modified_gmt,
            '',
            '',
            '',
            '',
            :post_name
        )
    ");
    $stmt->bindParam(":id", $post->post_id);
    $stmt->bindParam(":post_author", $author_id);
    $stmt->bindParam(":post_date", $post->post_dt);
    $stmt->bindParam(":post_date_gmt", $post->post_dt);
    $stmt->bindParam(":post_content", $post->post_content_xhtml);
    $stmt->bindParam(":post_title", $post->post_title);
    $stmt->bindParam(":post_modified", $post->post_dt);
    $stmt->bindParam(":post_modified_gmt", $post->post_dt);
    $stmt->bindParam(":post_name", $url);



    if ($stmt->execute()) {
        echo "Post $post->post_id créé avec succès.<br />";
    }
    // else {
    //     echo "<pre>";
    //     $stmt->debugDumpParams();
    //     print_r($db_new->errorInfo());
    //     die('Le programme a quitté en échec.');
    //     echo "</pre>";
    // }

    // CREATE TERMS RELATIONSHIPS
    $stmt = $db_new->prepare("INSERT INTO wp_term_relationships(
            object_id,
            term_taxonomy_id,
            term_order
        )
        VALUES(
            :object_id,
            :term_taxonomy_id,
            0
        )
    ");
    $stmt->bindParam(":object_id", $post->post_id);
    $stmt->bindParam(":term_taxonomy_id", $post->cat_id);

    if ($stmt->execute()) {
        echo "Term relationships $post->cat_id créé avec succès.<br />";
    }
    // else {
    //     echo "<pre>";
    //     $stmt->debugDumpParams();
    //     print_r($db_new->errorInfo());
    //     die('Le programme a quitté en échec.');
    //     echo "</pre>";
    // }

    // UPDATE CATEGORY COUNT

    // APPLY MODIFIERS
    if (!isset($posts_count[$post->cat_id])) {
        $posts_count[$post->cat_id] = 1;
    } else {
        $posts_count[$post->cat_id]++;
    }

    $stmt = $db_new->prepare("UPDATE wp_term_taxonomy
        SET count = :count
        WHERE term_id = :term_id
    ");

    $count = $posts_count[$post->cat_id];
    $stmt->bindParam(":count", $count);
    $stmt->bindParam(":term_id", $post->cat_id);

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