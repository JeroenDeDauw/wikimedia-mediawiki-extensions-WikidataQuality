<?php
$mapping = array(
    227 => array(
        19 => array(
            'nodeSelector' => '/record/datafield[@tag="551" and subfield[@code="i"]="Geburtsort"]/subfield[@code="a"]'
        ),
        20 => array(
            'nodeSelector' => '/record/datafield[@tag="551" and subfield[@code="i"]="Sterbeort"]/subfield[@code="a"]'
        ),
        22 => array(
            'nodeSelector' => '/record/datafield[@tag="500" and subfield[@code="9"]="v:Vater"]/subfield[@code="a"]',
            'valueFormatter' => 'concat(substring-after(./text(), ", "), " ", substring-before(./text(), ", "))'
        ),
        25 => array(
            'nodeSelector' => '/record/datafield[@tag="500" and subfield[@code="9"]="v:Mutter"]/subfield[@code="a"]',
            'valueFormatter' => 'concat(substring-after(./text(), ", "), " ", substring-before(./text(), ", "))'
        ),
        26 => array(
            'nodeSelector' => '/record/datafield[@tag="500" and subfield[@code="9"]="v:Ehemann" or subfield[@code="9"]="v:Ehefrau"]/subfield[@code="a"]',
            'valueFormatter' => 'concat(substring-after(./text(), ", "), " ", substring-before(./text(), ", "))'
        ),
        40 => array(
            'nodeSelector' => '/record/datafield[@tag="500" and subfield[@code="9"]="v:Sohn" or subfield[@code="9"]="v:Tochter"]/subfield[@code="a"]',
            'valueFormatter' => 'concat(substring-after(./text(), ", "), " ", substring-before(./text(), ", "))'
        ),
        106 => array(
            'nodeSelector' => '/record/datafield[@tag="550"]/subfield[@code="a"]'
        ),
        569 => array(
            'nodeSelector' => '/record/datafield[@tag="548" and subfield[@code="i"]="Exakte Lebensdaten"]/subfield[@code="a"]',
            'valueFormatter' => 'substring-before(./text(), "-")'
        ),
        570 => array(
            'nodeSelector' => '/record/datafield[@tag="548" and subfield[@code="i"]="Exakte Lebensdaten"]/subfield[@code="a"]',
            'valueFormatter' => 'substring-after(./text(), "-")'
        ),
        625 => array(
            'nodeSelector' => '/record/datafield[@tag="034" and subfield[@code="9"]="A:dgx"]/subfield[@code="d"]',
        ),
        1477 => array(
            'nodeSelector' => '/record/datafield[@tag="400" and subfield[@code="i"]="Wirklicher Name"]/subfield[@code="a"]',
            'valueFormatter' => 'concat(substring-after(./text(), ", "), " ", substring-before(./text(), ", "))'
        )
    )
);