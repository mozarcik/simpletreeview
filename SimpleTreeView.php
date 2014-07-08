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

    public $toolbarTemplate = '{searchInput}{collapse}{expand}';

    /**
    * @var array the configuration for buttons. Each array element specifies a single button
    * which has the following format:
    * <pre>
    * 'buttonID' => array(
    *     'label'    => '...',      // text label of the button
    *     'url'      => '...',      // URL of the button
    *     'imageUrl' => '...',      // image URL of the button. If not set or false, a text link is used
    *     'options'  => array(...), // HTML options for the button tag
    *     'click'    => '...',      // a JS function to be invoked when the button is clicked
    * )
    * </pre>
    *
    * Note that in order to display non-default buttons, the {@link toolbarTemplate} property needs to
    * be configured so that the corresponding button IDs appear as tokens in the template.
    */
    public $toolbarButtons = array();

    /**
     * JavaScript options for widget. Known options:
     *  * searchInput - search input selector for tree searching
     *  * expandIcon - CSS class used as the expand icon
     *  * collapseIcon - CSS class used as the collapse icon
     *
     * @var array
     */
    public $options = array();

    private $_buttonJs = array();

    public function init()
    {

        $this->initDefaultButtons();

        parent::init();

        $defaults = array(
            'searchInput' => '#search-tree',
        );
        $this->options = $this->options !== null ? CMap::mergeArray($defaults, $this->options) : $defaults;
    }

    public function run()
    {
        $this->renderToolbar();
        $this->renderItems($this->items, array('id' => $this->id));
        
        $this->registerClientScript();
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
            
            $itemHtmlOptions = array('class' => 'stv-item');
            
            echo CHtml::tag($this->itemHtmlTag, $itemHtmlOptions, strtr($this->itemTemplate, array(
                '{icon}'            => $icon,
                '{label}'           => $item['label'],
                '{rightControl}'    => isset($item['rightControl']) ? $item['rightControl'] : null,
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
        
		$options = CJavaScript::encode($this->options);
		$cs->registerScript(__CLASS__ . '#' . $this->id, "$('#{$this->id}').simpleTreeView($options);", CClientScript::POS_READY);

        if($this->_buttonJs !== array())
            Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$this->id.'-btns', implode("\n", $this->_buttonJs));
    }

    protected function renderToolbar()
    {
        $tr=array('{searchInput}' => CHtml::textField('search', '', array('placeholder' => Yii::t('app', 'Search'), 'class' => 'form-control', 'id' => 'search-tree')));
        ob_start();
        foreach($this->toolbarButtons as $id=>$button) {
            $this->renderButton($id, $button);
            $tr['{'.$id.'}']=ob_get_contents();
            ob_clean();
        }
        ob_end_clean();
        echo strtr($this->toolbarTemplate,$tr);
    }

    /**
    * Initializes the default buttons (collapse and expand).
    */
    protected function initDefaultButtons()
    {
        $this->toolbarButtons = CMap::mergeArray(array(
            'collapse' => array(
                'label' => Yii::t('app', 'Collapse all'),
                'url' => '#',
                'options' => array('id' => 'collapse-all', 'class' => 'btn btn-default'),
                'click' => "js:function(){ $('#$this->id').simpleTreeView('collapseAll'); return false; }",
            ),
            'expand' => array(
                'label' => Yii::t('app', 'Expand all'),
                'url' => '#',
                'options' => array('id' => 'expand-all', 'class' => 'btn btn-default'),
                'click' => "js:function(){ $('#$this->id').simpleTreeView('expandAll'); return false; }",
            ),
        ), $this->toolbarButtons);

        foreach($this->toolbarButtons as $id => $button) {
            if (strpos($this->toolbarTemplate,'{'.$id.'}')===false) {
                unset($this->toolbarButtons[$id]);
            } elseif (isset($button['click'])) {
                if (!isset($button['options']['class']))
                    $this->toolbarButtons[$id]['options']['class'] = $id;
                if (!($button['click'] instanceof CJavaScriptExpression))
                    $this->toolbarButtons[$id]['click'] = new CJavaScriptExpression($button['click']);
            }
        }
    }

    /**
    * Renders a link button.
    * @param string $id the ID of the button
    * @param array $button the button configuration which may contain 'label', 'url', 'imageUrl' and 'options' elements.
    * See {@link toolbarButtons} for more details.
    */
    protected function renderButton($id,$button)
    {
        $label = isset($button['label']) ? $button['label'] : $id;
        $url = isset($button['url']) ? $button['url'] : '#';
        $options = isset($button['options']) ? $button['options'] : array();

        $options['id'] = isset($button['id']) ? $button['id'] : "stv-btn-{$this->id}-$id";

        if(!isset($options['title']))
            $options['title']=$label;

        if(isset($button['imageUrl']) && is_string($button['imageUrl']))
            echo CHtml::link(CHtml::image($button['imageUrl'],$label),$url,$options);
        else
            echo CHtml::link($label,$url,$options);
        
        if (isset($button['click'])) {
            $function = CJavaScript::encode($button['click']);
            $this->_buttonJs[] = "jQuery(document).on('click','#{$options['id']}', $function);";
        }
    }

	/**
	 * Fetches a flat list from the DB and puts it into a tree-like array.
	 */
	public static function arrayToTree($data, $primaryKey = 'id', $parentKey = 'parent_id', $labelKey = 'name', $rightControlCallback = null) {
		$flat = array();
		$top = array();
		foreach($data as $item) {
            if (is_object($item)) {
                $primary_key = $item->{$primaryKey};
                $parent_key = $item->{$parentKey};
                $label = $item->{$labelKey};
            } elseif (is_array($item)) {
                $primary_key = $item[$primaryKey];
                $parent_key = $item[$parentKey];
                $label = $item[$labelKey];
            } else {
                throw new CException('Unsupported item passed to Simple Tree View widget.');
            }
			$flat[$primary_key] = array(
				'label' => $label,
                'item'  => $item,
				'items' => array(),
				'parent'=> null,
                'rightControl' => $rightControlCallback === null ? null : call_user_func($rightControlCallback, $item),
			);
			if ($parent_key !== null) {
				$flat[$parent_key]['items'][$primary_key] = &$flat[$primary_key];
				$flat[$primary_key]['parent'] = &$flat[$parent_key];
			} else {
				$top[$primary_key] = &$flat[$primary_key];
			}
		}
		return $top;
	}
}

