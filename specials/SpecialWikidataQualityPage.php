<?php

namespace WikidataQuality\Specials;


use SpecialPage;
use ValueFormatters\FormatterOptions;
use Wikibase\Lib\EntityIdHtmlLinkFormatter;
use Wikibase\Lib\EntityIdLinkFormatter;
use Wikibase\Lib\HtmlUrlFormatter;
use Wikibase\Lib\LanguageNameLookup;
use Wikibase\Lib\SnakFormatter;
use Wikibase\Lib\Store\LanguageLabelLookup;
use Wikibase\Repo\WikibaseRepo;


/**
 * Class SpecialWikidataQualityPage
 * @package WikidataQuality\Specials
 * @author BP2014N1
 * @license GNU GPL v2+
 */
abstract class SpecialWikidataQualityPage extends SpecialPage
{
    /**
     * @var \Wikibase\DataModel\Entity\EntityIdParser
     */
    protected $entityIdParser;

    /**
     * @var \Wikibase\Lib\Store\EntityLookup
     */
    protected $entityLookup;

    /**
     * @var \ValueFormatters\ValueFormatter
     */
    protected $dataValueFormatter;

    /**
     * @var EntityIdLinkFormatter
     */
    protected $entityIdLinkFormatter;

    /**
     * @var EntityIdHtmlLinkFormatter
     */
    protected $entityIdHtmlLinkFormatter;

    /**
     * @var HtmlUrlFormatter
     */
    protected $htmlUrlFormatter;


    /**
     * @param string $name
     * @param string $restriction
     * @param bool $listed
     * @param bool $function
     * @param string $file
     * @param bool $includable
     */
    public function __construct( $name = '', $restriction = '', $listed = true, $function = false, $file = '', $includable = false )
    {
        parent::__construct( $name, $restriction, $listed, $function, $file, $includable );

        $repo = WikibaseRepo::getDefaultInstance();

        // Get entity lookup
        $this->entityLookup = $repo->getEntityLookup();

        // Get entity id parser
        $this->entityIdParser = $repo->getEntityIdParser();

        // Get value formatter
        $formatterOptions = new FormatterOptions();
        $formatterOptions->setOption( SnakFormatter::OPT_LANG, $this->getLanguage()->getCode() );
        $this->dataValueFormatter = $repo->getValueFormatterFactory()->getValueFormatter( SnakFormatter::FORMAT_HTML, $formatterOptions );

        // Get entity id link formatters
        $entityTitleLookup = $repo->getEntityTitleLookup();
        $labelLookup = new LanguageLabelLookup( $repo->getTermLookup(), $this->getLanguage()->getCode() );
        $this->entityIdLinkFormatter = new EntityIdLinkFormatter( $entityTitleLookup );
        $this->entityIdHtmlLinkFormatter = new EntityIdHtmlLinkFormatter(
            $labelLookup,
            $entityTitleLookup,
            new LanguageNameLookup()
        );

        // Get url formatter
        $formatterOptions = new FormatterOptions();
        $this->htmlUrlFormatter = new HtmlUrlFormatter( $formatterOptions );
    }


    /**
     * @see SpecialPage::getGroupName
     *
     * @return string
     */
    function getGroupName()
    {
        return 'wikidataquality';
    }
}