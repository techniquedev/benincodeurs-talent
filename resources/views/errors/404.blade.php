@include('errors.layout', ['code' => 404, 'title' => 'Page introuvable', 'message' => "Cette page n'existe pas ou a été déplacée.", 'links' => [['Mon espace', '/espace'], ['Accueil', '/']]])
