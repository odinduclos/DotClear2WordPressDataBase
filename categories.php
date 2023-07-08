<?php

// GET CATEGORIES
$stmt = $db_old->prepare('SELECT * FROM dc_category WHERE 1');
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_OBJ);

foreach ($categories as $category) {

    // APPLY MODIFIERS
    $category->cat_id = $category->cat_id + $modifier_category;

    // CREATE TERMS
    $stmt = $db_new->prepare("INSERT INTO wp_terms(
            term_id,
            name,
            slug,
            term_group
        )
        VALUES(
            :term_id,
            :name,
            :slug,
            0
        )
    ");
    $stmt->bindParam(":term_id", $category->cat_id);
    $stmt->bindParam(":name", $category->cat_title);
    $stmt->bindParam(":slug", $category->cat_url);

    if ($stmt->execute()) {
        echo "Category $category->cat_id créé avec succès.<br />";
    }
    // else {
    //     echo "<pre>";
    //     $stmt->debugDumpParams();
    //     print_r($db_new->errorInfo());
    //     die('Le programme a quitté en échec.');
    //     echo "</pre>";
    // }

    // CREATE TERM TAXONOMY
    $stmt = $db_new->prepare("INSERT INTO wp_term_taxonomy(
            term_taxonomy_id,
            term_id,
            taxonomy,
            description,
            parent,
            count
        )
        VALUES(
            :term_taxonomy_id,
            :term_id,
            'category',
            '',
            0,
            1
        )
    ");
    $stmt->bindParam(":term_taxonomy_id", $category->cat_id);
    $stmt->bindParam(":term_id", $category->cat_id);

    if ($stmt->execute()) {
        echo "Term taxonomy $category->cat_id créé avec succès.<br />";
    }
    // else {
    //     echo "<pre>";
    //     $stmt->debugDumpParams();
    //     print_r($db_new->errorInfo());
    //     die('Le programme a quitté en échec.');
    //     echo "</pre>";
    // }
}