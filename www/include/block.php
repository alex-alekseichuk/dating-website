<?php

// framework

//include_once "html.php";


class CBlock
{
	var $m_blocks = Array();
	var $m_name = "";
	var $m_parent = null;
	var $m_root = null;

	function CBlock($name)
	{
		$this->m_name = $name;
		$this->m_root = $this;
	}

	function init()
	{
		foreach ($this->m_blocks as $n => $b )
		{
			//$b->init();
			$this->m_blocks[$n]->init();
		}
	}

	function action()
	{
		foreach ($this->m_blocks as $n => $b )
		{
			//$b->action();
			$this->m_blocks[$n]->action();
		}
	}

	function add(&$b)
	{
		if ($b->m_name == "")
			return;
		if (isset($this->m_blocks[$b->m_name]))
			return;
		$this->m_blocks[$b->m_name] = &$b; // php4.3: &$b    php5: $b   
		$b->m_parent = $this;
		$b->m_root = $this->m_root;
	}

}



class CHtmlBlock extends CBlock
{
	var $m_html = null;

	function CHtmlBlock($name, $html_path)
	{
		global $g_images;
		if (! isset($g_images))
			$g_images = "img";

		$this->CBlock($name);
		if ($html_path != null)
		{
			$this->m_html = new CHtml();
			$this->m_html->LoadTemplate($html_path , "main");
			$this->m_html->setvar("images" , $g_images);
			$this->m_html->setvar("params", get_params());
		}
	}

	function init()
	{
		parent::init();
	}

	function action()
	{
		parent::action();
	}


	// parse only this block
	// don't call this method
	function parseBlock(&$html)
	{
		//if ($this->m_name != "")
		if ($this->m_html != null)
		{
//echo "<hr>parse MAIN<hr>";
			$html->parse("main");
		}
		else
		{
//echo "<hr>parse " . $this->m_name . "<hr>";
			$html->parse($this->m_name);
			//$html->parse("main");
		}
	}


	// parse the block in blocks tree
	// call this method
	function parse($html)
	{
		if ($this->m_html != null)
		{
			$html_ = &$this->m_html;
		} else {
			if ($html == null)
				return;
			$html_ = &$html;
		}
		foreach ($this->m_blocks as $name => $b)
		{
			//$b->parse(&$html_);
			$this->m_blocks[$name]->parse(&$html_);
		}
		if ($this->m_html != null)
		{

			if ($this->m_parent == null)
			{
				$this->parseBlock($this->m_html);

				echo $this->m_html->getvar("main");

				//$this->m_html->pparse("main");
			} else {

				//$this->m_html->parse("main");
				$this->parseBlock($this->m_html);

//				if ($html != null)
					$html->setvar($this->m_name, $this->m_html->getvar("main"));
			}
		} else {
			//$html->parse($this->m_name);
			$this->parseBlock(&$html);
		}
	}

}


?>