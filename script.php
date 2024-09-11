<?php

// Récupérer l'URL de la page cible
$url = "";
if(isset($_POST['url'])){
    $url = $_POST['url'];
    $content = file_get_contents($url);

    // Chercher un lien vers un fichier mp3 dans les balises <a>, <li>, <source> et <audio>
    preg_match_all('/<a.*?href="(.*?\.mp3)".*?>|<li>.*?<a.*?href="(.*?\.mp3)".*?>|<source.*?src="(.*?\.mp3)".*?>|<audio.*?src="(.*?\.mp3)".*?>/', $content, $matches);
   
    $files = array_filter(array_merge($matches[1], $matches[2], $matches[3], $matches[4]));

    // Récupérer le titre de la page
    preg_match('/<title>(.*?)<\/title>/', $content, $titleMatches);
    $title = $titleMatches[1];

    // Chercher toutes les images sur la page
    preg_match_all('/<img.*?src="(.*?)".*?>/', $content, $imageMatches);
    $images = array_filter($imageMatches[1]);

    // Extraire le domaine de l'URL
    $parsedUrl = parse_url($url);
    $protocol = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
    $domain = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
    $baseDomain = $protocol . $domain;

    // Normaliser les liens des images
    foreach ($images as &$image) {
        // Si l'image est relative, ajouter le domaine
        if (parse_url($image, PHP_URL_SCHEME) === null) {
            if (strpos($image, '/') === 0) {
                // Cas d'une URL relative à la racine
                $image = $baseDomain . $image;
            } else {
                // Cas d'une URL relative
                $image = $baseDomain . '/' . $image;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Script PHP pour détecter un fichier mp3 et des images</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script>
        function copyToClipboard(url) {
            navigator.clipboard.writeText(url).then(function() {
                alert('URL copiée dans le presse-papier : ' + url);
            }, function(err) {
                console.error('Erreur lors de la copie : ', err);
            });
        }
    </script>

</head>
<body>
    <div class="container mt-5">
        <form method="post">
            <div class="mb-3">
                <label for="url" class="form-label">URL de la page cible:</label>
                <input type="text" name="url" id="url" class="form-control" value="<?php echo htmlspecialchars($url); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Analyser</button>
        </form>

        <?php if(!empty($files) && !empty($title)): ?>
            <div class="mt-3">
                <label for="pageTitle" class="form-label">Titre de la page:</label>
                <input type="text" id="pageTitle" class="form-control" value="<?php echo htmlspecialchars($title); ?>" readonly>
            </div>
            <div class="mt-3">
                <?php foreach($files as $file): ?>
                    <label for="mp3Url" class="form-label">URL du fichier mp3:</label>
                    <input type="text" id="mp3Url" class="form-control" value="<?php echo htmlspecialchars($file); ?>" readonly>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($images)): ?>
            <div class="mt-3">
                <h5>Images trouvées :</h5>
                <div class="row">
                    <?php foreach($images as $image): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <img src="<?php echo htmlspecialchars($image); ?>" class="card-img-top" alt="Image" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($image); ?></h5>
                                    <a href="<?php echo htmlspecialchars($image); ?>" class="btn btn-primary" target="_blank">Voir l'image</a>
                                    <button class="btn btn-secondary mt-2" onclick="copyToClipboard('<?php echo htmlspecialchars($image); ?>')">Copier l'URL</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
