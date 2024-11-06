<?php
namespace VMMHelper;

defined('ABSPATH') || exit;

class UIkitHelper {

    public static function convertElementorToUIkit($dom) {
        $xpath = new \DOMXPath($dom);

        // Convert Elementor Toggles to UIkit Accordions
        self::convertToggles($xpath);

        return $dom;
    }

    private static function convertToggles($xpath) {
        $toggles = $xpath->query("//div[@data-widget_type='toggle.default']");

        foreach ($toggles as $toggle) {
            $elementorToggle = $xpath->query(".//div[contains(@class, 'elementor-toggle')]", $toggle)->item(0);
            if ($elementorToggle) {
                $elementorToggle->setAttribute('uk-accordion', '');

                $toggleItems = $xpath->query(".//div[contains(@class, 'elementor-toggle-item')]", $elementorToggle);
                foreach ($toggleItems as $item) {
                    $title = $xpath->query(".//div[contains(@class, 'elementor-tab-title')]", $item)->item(0);
                    $content = $xpath->query(".//div[contains(@class, 'elementor-tab-content')]", $item)->item(0);

                    if ($title && $content) {
                        $title->setAttribute('class', $title->getAttribute('class') . ' uk-accordion-title');
                        $content->setAttribute('class', $content->getAttribute('class') . ' uk-accordion-content');
                    }
                }
            }
        }
    }
}
