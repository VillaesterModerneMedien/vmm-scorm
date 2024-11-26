<?php
namespace VMMHelper;

defined('ABSPATH') || exit;

class UIkitHelper {

    public static function convertElementorToUIkit($dom) {
        $xpath = new \DOMXPath($dom);
        $toggles = $xpath->query("//div[@data-widget_type='toggle.default']");
        $accordions = $xpath->query("//div[@data-widget_type='accordion.default']"); // Add this line
        $alerts = $xpath->query("//div[@data-widget_type='alert.default']"); // Add this line
        $sliders = $xpath->query("//div[contains(@class, 'elementor-main-swiper')]");
        $videos = $xpath->query("//div[contains(@class, 'elementor-widget-video')]");
        $tabs = $xpath->query("//div[@data-widget_type='tabs.default']");
        $progressBars = $xpath->query("//div[contains(@class, 'elementor-progress-bar')]");
        $imageCarousels = $xpath->query("//div[@data-widget_type='image-carousel.default']");
        $imageGalleries = $xpath->query("//div[@data-widget_type='image-gallery.default']");

        if ($videos->length > 0) {
            self::convertVideos($xpath, $videos, $dom);
        }

        if ($toggles->length > 0) {
            self::convertToggles($xpath, $toggles);
        }
        if ($accordions->length > 0) { // Add this block
            self::convertAccordions($xpath, $accordions);
        }

        if ($sliders->length > 0) {
            self::convertSliders($xpath, $sliders, $dom);
        }

        if ($alerts->length > 0) { // Add this block
            self::convertAlerts($xpath, $alerts, $dom);
        }

        if ($tabs->length > 0) {
            self::convertTabs($xpath, $tabs, $dom);
        }
        if ($progressBars->length > 0) {
            self::convertProgressBars($xpath, $progressBars);
        }

        if ($imageCarousels->length > 0) {
            self::convertImageCarousel($xpath, $imageCarousels, $dom);
        }

        if ($imageGalleries->length > 0) {
            self::convertImageGallery($xpath, $imageGalleries, $dom);
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

    private static function convertAccordions($xpath, $accordions) {
        foreach ($accordions as $accordion) {
            $elementorAccordion = $xpath->query(".//div[contains(@class, 'elementor-accordion')]", $accordion)->item(0);
            if ($elementorAccordion) {
                $elementorAccordion->setAttribute('uk-accordion', '');

                $accordionItems = $xpath->query(".//div[contains(@class, 'elementor-accordion-item')]", $elementorAccordion);
                foreach ($accordionItems as $item) {
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

    private static function convertVideos($xpath, $videos, $dom) {
        foreach ($videos as $video) {
            // Check if it's a lightbox video
            $lightboxData = $xpath->query(".//div[@data-elementor-open-lightbox='yes']", $video)->item(0);

            if ($lightboxData) {
                // Handle lightbox video
                $lightboxSettings = json_decode(html_entity_decode($lightboxData->getAttribute('data-elementor-lightbox')), true);
                $videoUrl = $lightboxSettings['url'];

                // Create new relative video path
                $videoFilename = basename($videoUrl);
                $relativeVideoUrl = "../videos/" . $videoFilename;

                // Create wrapper div
                $wrapper = $dom->createElement('div');
                $wrapper->setAttribute('uk-lightbox', '');

                // Create link for lightbox
                $link = $dom->createElement('a');
                $link->setAttribute('href', $relativeVideoUrl);
                $link->setAttribute('role', 'button');

                // Get and set thumbnail image
                $thumbnail = $xpath->query(".//img", $video)->item(0);
                if ($thumbnail) {
                    $newThumb = $thumbnail->cloneNode(true);
                    $link->appendChild($newThumb);
                }

                $wrapper->appendChild($link);
                $video->parentNode->replaceChild($wrapper, $video);
            } else {
                // Handle embedded video
                $existingVideo = $xpath->query(".//video", $video)->item(0);
                if ($existingVideo) {
                    $newVideo = $dom->createElement('video');

                    // Copy attributes from existing video
                    $newVideo->setAttribute('src', $existingVideo->getAttribute('src'));
                    $newVideo->setAttribute('controls', '');
                    $newVideo->setAttribute('class', 'el-image');

                    if ($existingVideo->hasAttribute('poster')) {
                        $newVideo->setAttribute('poster', $existingVideo->getAttribute('poster'));
                    }

                    $video->parentNode->replaceChild($newVideo, $video);
                }
            }
        }
    }

    private static function convertAlerts($xpath, $alerts, $dom) {
        foreach ($alerts as $alert) {
            $elementorAlert = $xpath->query(".//div[contains(@class, 'elementor-alert')]", $alert)->item(0);
            if ($elementorAlert) {
                // Add UIkit alert attribute
                $elementorAlert->setAttribute('uk-alert', '');

                // Remove existing dismiss button
                $dismissButton = $xpath->query(".//button[contains(@class, 'elementor-alert-dismiss')]", $elementorAlert)->item(0);
                if ($dismissButton) {
                    $dismissButton->parentNode->removeChild($dismissButton);
                }

                // Add new UIkit close button at the beginning of alert
                $newDismiss = $dom->createElement('a');
                $newDismiss->setAttribute('class', 'uk-alert-close');
                $newDismiss->setAttribute('uk-close', '');
                $newDismiss->setAttribute('href', '#');
                $elementorAlert->insertBefore($newDismiss, $elementorAlert->firstChild);
            }
        }
    }

    private static function convertTabs($xpath, $tabs, $dom) {
        foreach ($tabs as $tab) {
            // Remove style element
            $styleElement = $xpath->query(".//style", $tab)->item(0);
            if ($styleElement) {
                $styleElement->parentNode->removeChild($styleElement);
            }

            // Convert tab titles wrapper
            $tabsWrapper = $xpath->query(".//div[contains(@class, 'elementor-tabs-wrapper')]", $tab)->item(0);
            if ($tabsWrapper) {
                $tabsWrapper->setAttribute('class', 'uk-subnav uk-subnav-pill');
                $tabsWrapper->setAttribute('uk-switcher', '');

                // Convert tab titles to links
                $tabTitles = $xpath->query(".//div[contains(@class, 'elementor-tab-desktop-title')]", $tabsWrapper);
                foreach ($tabTitles as $title) {
                    $titleText = $title->textContent;
                    $link = $dom->createElement('a');
                    $link->setAttribute('href', '#');
                    $link->textContent = $titleText;
                    $title->textContent = '';
                    $title->removeAttribute('class');
                    $title->appendChild($link);
                }
            }

            // Convert content wrapper
            $contentWrapper = $xpath->query(".//div[contains(@class, 'elementor-tabs-content-wrapper')]", $tab)->item(0);
            if ($contentWrapper) {
                $contentWrapper->setAttribute('class', 'uk-switcher uk-margin');

                // Remove mobile titles
                $mobileTitles = $xpath->query(".//div[contains(@class, 'elementor-tab-mobile-title')]", $contentWrapper);
                foreach ($mobileTitles as $mobileTitle) {
                    $mobileTitle->parentNode->removeChild($mobileTitle);
                }

                // Clean content divs
                $contentDivs = $xpath->query(".//div[contains(@class, 'elementor-tab-content')]", $contentWrapper);
                foreach ($contentDivs as $div) {
                    $div->removeAttribute('class');
                    $div->removeAttribute('hidden');
                }
            }

            // Remove elementor classes from main tab container
            $tabContainer = $xpath->query(".//div[contains(@class, 'elementor-tabs')]", $tab)->item(0);
            if ($tabContainer) {
                $tabContainer->removeAttribute('class');
            }
        }
    }

    private static function convertProgressBars($xpath, $progressBars) {
        foreach ($progressBars as $progressBar) {
            if ($progressBar->hasAttribute('data-max')) {
                $percentage = $progressBar->getAttribute('data-max');
                $progressBar->setAttribute('style', 'width:' . $percentage . '%');
            }
        }
    }

    private static function convertImageCarousel($xpath, $carousels, $dom) {
        foreach ($carousels as $carousel) {
            $settings = json_decode(html_entity_decode($carousel->getAttribute('data-settings')), true);
            $slidesToShow = isset($settings['slides_to_show']) ? $settings['slides_to_show'] : '3';

            $carousel->setAttribute('class', 'uk-slider');
            $carousel->setAttribute('uk-slider', 'sets: true');

            // Create slides wrapper with lightbox
            $slidesWrapper = $dom->createElement('div');
            $gridClass = 'uk-slider-items uk-grid uk-grid-small';
            $gridClass .= ' uk-child-width-1-' . $slidesToShow . '@m';
            $gridClass .= ' uk-child-width-1-2@s';
            $gridClass .= ' uk-child-width-1-1';
            $slidesWrapper->setAttribute('class', $gridClass);
            $slidesWrapper->setAttribute('uk-lightbox', '');

            $originalSlides = $xpath->query(".//div[contains(@class, 'swiper-slide')]", $carousel);
            if ($originalSlides->length > 0) {
                foreach ($originalSlides as $slide) {
                    $newSlide = $dom->createElement('div');
                    $link = $xpath->query(".//a", $slide)->item(0);
                    $image = $xpath->query(".//img", $slide)->item(0);

                    if ($link && $image) {
                        // Create lightbox link with full-size image URL
                        $newLink = $dom->createElement('a');
                        $newLink->setAttribute('href', $link->getAttribute('href'));
                        $newLink->setAttribute('data-caption', $image->getAttribute('alt'));

                        // Use thumbnail image for display
                        $newLink->appendChild($image->cloneNode(true));
                        $newSlide->appendChild($newLink);
                        $slidesWrapper->appendChild($newSlide);
                    }
                }
            }

            // Navigation elements
            $prevLink = $dom->createElement('a');
            $prevLink->setAttribute('class', 'uk-slidenav uk-position-center-left uk-position-small');
            $prevLink->setAttribute('uk-slidenav-previous', '');
            $prevLink->setAttribute('uk-slider-item', 'previous');
            $prevLink->setAttribute('href', '#');

            $nextLink = $dom->createElement('a');
            $nextLink->setAttribute('class', 'uk-slidenav uk-position-center-right uk-position-small');
            $nextLink->setAttribute('uk-slidenav-next', '');
            $nextLink->setAttribute('uk-slider-item', 'next');
            $nextLink->setAttribute('href', '#');

            $dotnav = $dom->createElement('ul');
            $dotnav->setAttribute('class', 'uk-slider-nav uk-dotnav uk-flex-center uk-margin');

            $fragment = $dom->createDocumentFragment();
            $fragment->appendChild($slidesWrapper);
            $fragment->appendChild($prevLink);
            $fragment->appendChild($nextLink);
            $fragment->appendChild($dotnav);

            while ($carousel->hasChildNodes()) {
                $carousel->removeChild($carousel->firstChild);
            }

            $carousel->appendChild($fragment);
        }
    }

    private static function convertImageGallery($xpath, $galleries, $dom) {
        foreach ($galleries as $gallery) {
            // Get gallery container
            $galleryContainer = $xpath->query(".//div[contains(@class, 'elementor-image-gallery')]", $gallery)->item(0);
            if ($galleryContainer) {
                // Add lightbox attribute to gallery container
                $galleryContainer->setAttribute('uk-lightbox', '');

                // Process all gallery items
                $galleryItems = $xpath->query(".//figure[contains(@class, 'gallery-item')]", $galleryContainer);
                foreach ($galleryItems as $item) {
                    // Get existing link and image
                    $link = $xpath->query(".//a", $item)->item(0);
                    $image = $xpath->query(".//img", $item)->item(0);

                    if ($link && $image) {
                        // Get the full size image URL from the link
                        $fullSizeUrl = $link->getAttribute('href');

                        // Create new link with UIkit attributes
                        $newLink = $dom->createElement('a');
                        $newLink->setAttribute('href', $fullSizeUrl);
                        $newLink->setAttribute('data-caption', $image->getAttribute('alt'));

                        // Move image to new link
                        $newLink->appendChild($image->cloneNode(true));

                        // Replace old link with new one
                        $link->parentNode->replaceChild($newLink, $link);
                    }
                }
            }
        }
    }

}

