<?php
namespace VMMHelper;

defined('ABSPATH') || exit;

class UIkitHelper {

    public static function convertElementorToUIkit($dom) {
        $xpath = new \DOMXPath($dom);
        $toggles = $xpath->query("//div[@data-widget_type='toggle.default']");
        $sliders = $xpath->query("//div[contains(@class, 'elementor-main-swiper')]");

        if ($toggles->length > 0) {
            self::convertToggles($xpath, $toggles);
        }


        if ($sliders->length > 0) {
            self::convertSliders($xpath, $sliders, $dom);
        }

        return $dom;
    }

    private static function convertToggles($xpath, $toggles) {
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
    private static function convertSliders($xpath, $sliders, $dom) {
        foreach ($sliders as $slider) {
            // Add UIkit slider attributes to main container
            $slider->setAttribute('uk-slider', '');
            $slider->setAttribute('class', $slider->getAttribute('class') . ' uk-position-relative uk-light');

            // Convert slides wrapper
            $slidesWrapper = $xpath->query(".//div[contains(@class, 'swiper-wrapper')]", $slider)->item(0);
            if ($slidesWrapper) {
                $slidesWrapper->setAttribute('class', $slidesWrapper->getAttribute('class') . ' uk-slider-items uk-grid');
            }

            // Convert individual slides
            $slides = $xpath->query(".//div[contains(@class, 'swiper-slide')]", $slidesWrapper);
            foreach ($slides as $slide) {
                $slide->setAttribute('class', $slide->getAttribute('class') . ' uk-width-1-1');
            }

            // Convert navigation buttons
            $prevButton = $xpath->query(".//div[contains(@class, 'elementor-swiper-button-prev')]", $slider)->item(0);
            $nextButton = $xpath->query(".//div[contains(@class, 'elementor-swiper-button-next')]", $slider)->item(0);

            if ($prevButton) {
                // Create new UIkit previous link
                $prevLink = $dom->createElement('a');
                $prevLink->setAttribute('class', 'uk-position-center-left uk-position-small uk-hidden-hover');
                $prevLink->setAttribute('href', '#');
                $prevLink->setAttribute('uk-slidenav-previous', '');
                $prevLink->setAttribute('uk-slider-item', 'previous');

                // Replace the original button with the new link
                $prevButton->parentNode->replaceChild($prevLink, $prevButton);
            }

            if ($nextButton) {
                // Create new UIkit next link
                $nextLink = $dom->createElement('a');
                $nextLink->setAttribute('class', 'uk-position-center-right uk-position-small uk-hidden-hover');
                $nextLink->setAttribute('href', '#');
                $nextLink->setAttribute('uk-slidenav-next', '');
                $nextLink->setAttribute('uk-slider-item', 'next');

                // Replace the original button with the new link
                $nextButton->parentNode->replaceChild($nextLink, $nextButton);
            }

            // Convert pagination
            $pagination = $xpath->query(".//div[contains(@class, 'swiper-pagination')]", $slider)->item(0);
            if ($pagination && $pagination->parentNode) {
                $newPagination = $dom->createElement('ul');
                $newPagination->setAttribute('class', 'uk-slider-nav uk-dotnav uk-flex-center uk-margin');
                $pagination->parentNode->replaceChild($newPagination, $pagination);
            }


        }
    }



}

