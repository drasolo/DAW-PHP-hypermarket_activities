<?php

final class DabItParser
{
    /**
     * Best-effort: caută linkuri către produse și încearcă să extragă nume + preț.
     * (Depinde de structura HTML curentă a site-ului.)
     */
    public static function parseProducts(string $html, int $limit = 10): array
    {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xp = new DOMXPath($dom);

        $out = [];

        // Încearcă mai multe pattern-uri comune
        $candidates = $xp->query("//a[contains(@href,'produs') or contains(@href,'product') or contains(@class,'product')]");
        foreach ($candidates as $a) {
            if (count($out) >= $limit) break;

            $name = trim(preg_replace('/\s+/', ' ', $a->textContent));
            $href = $a->getAttribute('href');

            if ($name === '' || mb_strlen($name) < 3) continue;

            // încearcă să găsească preț în vecinătate (card)
            $parent = $a->parentNode;
            $price = '';

            for ($i=0; $i<4 && $parent; $i++) {
                $priceNode = $xp->query(".//*[contains(text(),'lei') or contains(text(),'RON') or contains(@class,'price')]", $parent)->item(0);
                if ($priceNode) {
                    $price = trim(preg_replace('/\s+/', ' ', $priceNode->textContent));
                    break;
                }
                $parent = $parent->parentNode;
            }

            $out[] = [
                'name' => $name,
                'price' => $price,
                'href' => $href,
            ];
        }

        // dedupe by name
        $uniq = [];
        $final = [];
        foreach ($out as $row) {
            $k = mb_strtolower($row['name']);
            if (isset($uniq[$k])) continue;
            $uniq[$k] = true;
            $final[] = $row;
        }

        return array_slice($final, 0, $limit);
    }
}
