<?php

function sanitize($input)
{
    // Autorise certaines balises HTML mais supprime les scripts
    return strip_tags(
        $input,
        '<p><br><b><i><u><strong><em><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><pre><code>'
    );
}
