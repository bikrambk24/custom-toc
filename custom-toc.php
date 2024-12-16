<?php
/*
Plugin Name: Custom Table of Contents
Description: Displays a table of contents for blog posts using H2 and H3 tags.
Version: 1.02
Author: Bikram Bk
*/


function ctoc_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('ctoc-script', plugin_dir_url(__FILE__) . 'js/ctoc-script.js', array('jquery'), '1.0', true);
    wp_enqueue_style('ctoc-style', plugin_dir_url(__FILE__) . 'css/ctoc-style.css');
}
add_action('wp_enqueue_scripts', 'ctoc_enqueue_scripts');

function ctoc_generate_toc($content) {
    if (!is_single()) {
        return $content;
    }

    // Use libxml to handle errors internally
    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $wrapped_content = '<?xml encoding="UTF-8">' . '<html><body>' . $content . '</body></html>';
    $dom->loadHTML($wrapped_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    // Clear any XML errors that have been thrown
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    // Check if there are any H2 tags
    $h2_tags = $xpath->query('//h2');
    
    if ($h2_tags->length == 0) {
        // If no H2 tags, don't generate TOC
        return $content;
    }

    // If H2 tags exist, proceed with TOC generation
    $toc = '<div class="ctoc-wrapper"><h4>Table of Contents</h4><ul class="ctoc-list">';
    $h2_count = 0;
    $h3_count = 0;
    $current_h2 = null;

    $headers = $xpath->query('//h2|//h3');

    foreach ($headers as $header) {
        if ($header->nodeName == 'h2') {
            if ($h3_count > 0) {
                $toc .= '</ul></li>';
                $h3_count = 0;
            }
            $h2_count++;
            $current_h2 = $header;
            $id = 'ctoc-' . $h2_count;
            $header->setAttribute('id', $id);
            $toc .= '<li class="ctoc-h2">';
            $toc .= '<a href="#' . $id . '" class="ctoc-h2-link">' . $header->nodeValue;
            
            // Check if there are H3 tags following this H2
            $next = $header->nextSibling;
            $has_h3 = false;
            while ($next && $next->nodeName != 'h2') {
                if ($next->nodeName == 'h3') {
                    $has_h3 = true;
                    break;
                }
                $next = $next->nextSibling;
            }
            
            if ($has_h3) {
                $toc .= '<span class="ctoc-toggle">&#43;</span>';
            }
            $toc .= '</a>';
            
            if ($has_h3) {
                $toc .= '<ul class="ctoc-h3-list" style="display:none;">';
            }
        } elseif ($header->nodeName == 'h3' && $current_h2 !== null) {
            $h3_count++;
            $id = 'ctoc-' . $h2_count . '-' . $h3_count;
            $header->setAttribute('id', $id);
            $toc .= '<li class="ctoc-h3"><a href="#' . $id . '">' . $header->nodeValue . '</a></li>';
        }
    }

    if ($h3_count > 0) {
        $toc .= '</ul>';
    }
    $toc .= '</li></ul></div>';

    global $ctoc_content;
    $ctoc_content = $toc;

    // Extract only the body content
    $body = $dom->getElementsByTagName('body')->item(0);
    $inner_html = '';
    foreach ($body->childNodes as $child) {
        $inner_html .= $dom->saveHTML($child);
    }

    return $inner_html; // Return content without TOC
}

function ctoc_shortcode() {
    global $ctoc_content;
    return isset($ctoc_content) ? $ctoc_content : '';
}

add_filter('the_content', 'ctoc_generate_toc');
add_shortcode('custom_toc', 'ctoc_shortcode');
