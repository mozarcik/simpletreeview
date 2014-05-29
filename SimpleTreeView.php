<?php

class SimpleTreeView extends CWidget
{
    /**
	 * @var array the HTML options for the view container tag.
	 */
	public $htmlOptions=array();

    /**
     * @var array Items in format array('label' => 'Label', 'items' => array(), 'htmlOptions' => array())
     */
    public $items = array();

    public $toggleIconHtml = '<i class="fa fa-lg fa-expand-o toggle"></i>';

    public $itemTemplate = '{icon}<span class="title">{label}</span><span class="right-control">{rightControl}</span>';

    public $itemHtmlTag = 'div';

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $this->registerClientScript();
        $this->renderItems($this->items, array('id' => $this->id));
    }

    protected function renderItems($items = array(), $htmlOptions = array())
    {
        if (empty($items))
            return;


        $htmlOptions['class'] = (isset($htmlOptions['class']) ? $htmlOptions['class'] : '') . ' stv-list';
        
        echo CHtml::tag('ul', $htmlOptions, false, false);
        foreach ($items as $item) {
            echo '<li>';
            $icon = '';
            if (isset($item['items']) && !empty($item['items']))
                $icon = $this->toggleIconHtml;
            
            $rightControl = '';
            if (isset($item['rightControl'])) {
                $rightControl = $item['rightControl'];
            }
            $itemHtmlOptions = array('class' => 'stv-item');
            
            echo CHtml::tag($this->itemHtmlTag, $itemHtmlOptions, strtr($this->itemTemplate, array(
                '{icon}'            => $icon,
                '{label}'           => $item['label'],
                '{rightControl}'    => $rightControl,
            )));

            if (isset($item['items']) && !empty($item['items']))
                $this->renderItems($item['items'], isset($item['htmlOptions']) ? $item['htmlOptions'] : array());

            echo '</li>';
        }
        echo '</ul>';
    }

    protected function registerClientScript()
    {
        $file=dirname(__FILE__).'/assets';
		$assets = Yii::app()->getAssetManager()->publish($file);
		$cs = Yii::app()->clientScript;
		$cs->registerCssFile($assets . '/stv.css');
		$cs->registerScriptFile($assets . '/stv.js');
//		$optionsArr = $this->options !== null ? CMap::mergeArray($this->defaultOptions, $this->options) : $this->defaultOptions;
//		$optionsArr['eventSources'] = $this->eventSources;
		$options = CJavaScript::encode(array());
		$cs->registerScript(__CLASS__ . '#' . $this->id, "$('#{$this->id}').simpleTreeView($options);", CClientScript::POS_READY);
    }
}

