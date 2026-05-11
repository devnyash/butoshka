<?php
header('Content-Type: application/xml; charset=utf-8');

require_once('db.php');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    
    <url>
        <loc>https://butoshka.psychoware.website/index.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    
    <url>
        <loc>https://butoshka.psychoware.website/korzina.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    
    <url>
        <loc>https://butoshka.psychoware.website/avtoris.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    
    <url>
        <loc>https://butoshka.psychoware.website/regist.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    
    <?php
    $sql = "SELECT id, name FROM products ORDER BY id DESC";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($product = $result->fetch_assoc()) {
            $url = 'https://butoshka.psychoware.website/index.php#product-' . $product['id'];
            ?>
            <url>
                <loc><?= $url ?></loc>
                <lastmod><?= date('Y-m-d') ?></lastmod>
                <changefreq>weekly</changefreq>
                <priority>0.8</priority>
            </url>
            <?php
        }
    }
    ?>
    
</urlset>