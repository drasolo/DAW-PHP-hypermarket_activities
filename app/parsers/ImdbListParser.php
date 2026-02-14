<?php

final class ImdbListParser
{
    /**
     * Returnează primele N intrări (titlu + an + rating, dacă există)
     */
    public static function parseList(string $html, int $limit = 10): array
    {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xp = new DOMXPath($dom);

        // Pe paginile IMDB list, fiecare item e în lister-item-content
        $items = $xp->query("//div[contains(@class,'lister-item-content')]");
        $out = [];

        foreach ($items as $item) {
            if (count($out) >= $limit) break;

            $titleNode = $xp->query(".//h3[contains(@class,'lister-item-header')]/a", $item)->item(0);
            if (!$titleNode) continue;

            $title = trim($titleNode->textContent);

            $yearNode = $xp->query(".//h3[contains(@class,'lister-item-header')]//span[contains(@class,'lister-item-year')]", $item)->item(0);
            $year = $yearNode ? trim($yearNode->textContent) : '';

            $ratingNode = $xp->query(".//span[contains(@class,'ipl-rating-star__rating')]", $item)->item(0);
            $rating = $ratingNode ? trim($ratingNode->textContent) : '';

            $out[] = [
                'title' => $title,
                'year' => $year,
                'rating' => $rating,
            ];
        }

        return $out;
    }
}
