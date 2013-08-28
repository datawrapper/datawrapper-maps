<?php

class DatawrapperPlugin_VisualizationMaps extends DatawrapperPlugin_Visualization {

    function __construct() {
        $this->maps           = null;
        $this->maps_as_option = null;
    }

    public function init() {
        parent::init();
        $plugin = $this;
        global $app;
        DatawrapperHooks::register(
            DatawrapperHooks::VIS_OPTION_CONTROLS,
            function($o, $k) use ($app, $plugin) {
                $env = array('option' => $o, 'key' => $k);
                $app->render('plugins/' . $plugin->getName() . '/controls.twig', $env);
            }
        );
    }

    private function getMaps() {
        if (!empty($this->maps)) return $this->maps;
        $maps = scandir(dirname(__FILE__).'/static/maps');
        $maps = array_filter($maps, function($var){return strncmp($var, ".", 1);});
        $this->maps = $maps;
        return $maps;
    }

    private function getMapsAsOption() {
        if (!empty($this->maps_as_option)) return $this->maps_as_option;
        $res = array();
        $locale = substr(DatawrapperSession::getLanguage(), 0, 2);
        foreach ($this->getMaps() as $map) {
            $json = json_decode(file_get_contents(dirname(__FILE__).'/static/maps/'.$map.'/map.json'), true);
            $label = $map;
            if (!empty($json['title'])) {
                if (!empty($json['title'][$locale])) {
                    $label = $json['title'][$locale];
                } elseif (!empty($json['title']['en'])) {
                    $label = $json['title']['en'];
                }
            }
            if (!empty($json['keys'])) {
                $keys = $json['keys'];
            } else {
                $keys = array();
            }
            $res[] = array(
                'keys'  => $keys,
                'value' => $map,
                'label' => $label
            );
        }
        $this->maps_as_option = $res;
        return $res;
    }

    private function getAssets() {
        $assets = array();
        foreach ($this->getMaps() as $map) {
            $assets[] = "maps/".$map."/map.json";
            $assets[] = "maps/".$map."/map.svg";
        }
        return $assets;
    }

    private function getOptions() {
        $id = $this->getName();
        return array(
            "---map-options---" => array(
                "type" => "separator",
                "label" => "Select and customize display"
            ),
            "map" => array(
                "type"    => "map-selector",
                "label"   => __("Select map", $id),
                "options" => $this->getMapsAsOption(),
            ),
            "scale-mode" => array(
                "type" => "radio",
                "label" => __("Scale mode"),
                "options" => array(
                    array(
                        "value" => "width",
                        "label" => __("Scale map to chart width")
                    ),
                    array(
                        "value" => "viewport",
                        "label" => __("Fit map into chart")
                    )
                )
            ),
            "---color-options---" => array(
                "type" => "separator",
                "label" => "Customize map colors"
            ),
            "gradient" => array(
                "type" => "color-gradient-selector",
                "label" => __("Color gradient", $id),
                "locale" => array(
                    "number of classes" => __("Number of classes", $id),
                    "breaks type" => __("Breaks type", $id)
                ),
                "color-axis" => "color"
            )
        );
    }

    public function getMeta() {
        $id = $this->getName();
        return array(
            "id" => "maps",
            "extends" => "raphael-chart",
            "libraries" => array(
                array(
                    "local" => "vendor/kartograph.min.js",
                    "cdn" => "//assets-datawrapper.s3.amazonaws.com/vendor/kartograph.js/0.7.1/kartograph.min.js"
                )
            ),
            "title"   => __("Maps", $id),
            "order"   => 62,
            "axes"    => array(
                "keys" => array(
                    "accepts" => array("text", "number"),
                ),
                "color" => array(
                    "accepts" => array("number")
                )
            ),
            "hide-base-color-selector" => true,
            "assets"  => $this->getAssets(),
            "options" => $this->getOptions()
        );
    }

    public function getDemoDataSets(){
        $id = $this->getName();
        $datasets = array();
        $datasets[] = array(
            'id' => 'unemployment-rate-in-the-european-union',
            'title' => __('Unemployment rate in the European Union', $id),
            'type' => __('Europe Map', $id),
            'presets' => array(
                'type' => 'maps',
                'metadata.describe.intro' => __("The unemployment rate is the percentage of unemployed in the labor force, on the basis of the definition of the International Labour Organization (ILO).", $id),
                'title' => __('Unemployment rate in the European Union', $id),
                'metadata.describe.source-name' => 'Eurostat',
                'metadata.describe.source-url' => 'http://epp.eurostat.ec.europa.eu/tgm/table.do?tab=table&init=1&plugin=1&language=fr&pcode=teilm020',
                'metadata.data.vertical-header' => true,
                'metadata.data.transpose' => false,
                'metadata.visualize.map' => 'europe'
            ),
            'data' => "code ISO;Taux de chomage Janvier 2012\nDE;5,4\nAT;4,8\nBE;8,1\nBG;12,6\nCY;13,6\nDK;7,4\nES;26,4\nEE;9,9\nFI;8\nFR;10,8\nGR;26,7\nHU;11,2\nIE;14,1\nIT;11,7\nLV;\nLT;13,1\nLU;5,4\nMT;6,7\nNL;6\nPL;10,6\nPT;17,5\nCZ;7,1\nRO;6,6\nGB;7,8\nSK;14,5\nSI;9,6\nSE;8"
        );
        return $datasets;
    }
}
