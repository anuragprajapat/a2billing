<?php

class ElemGraph extends ElemBase {
	public $sums = array();
	public $plots = array();
	public $styles = array();
	public $graphtype = null;
	protected $graph = null;
	
	
	public function RenderHeadGraph(){
			
		switch($this->styles[type]){
		case 'pie':
			require_once(DIR_COMMON."jpgraph_lib/jpgraph_pie.php");
			require_once(DIR_COMMON."jpgraph_lib/jpgraph_pie3d.php");
			$this->graph = new PieGraph(600,450,"auto");
			break;
		default:
			require_once(DIR_COMMON."jpgraph_lib/jpgraph_line.php");
			require_once(DIR_COMMON."jpgraph_lib/jpgraph_bar.php");
			$this->graph = new Graph(600,450);
		}
		
		$this->graph->SetMargin(40,40,45,90);
		$this->graph->SetFrame(false);
		$this->graph->SetScale("textlin");
		$this->graph->yaxis->scale->SetGrace(3);
		
		if ($form->FG_DEBUG > 1)
			echo "RenderGraph!\n";
	}
	
	
	public function Render(){
	}
	
	
	public function RenderGraph(&$form){
		
		$gmode= $form->getpost_single('graph');
		$this->graphtype = $this->styles[type];
		
		if ($this->plots[type]=='sums'){
			$sum_objt = new SumMultiView();
			$sum_objt->sums[] = $this->plots[data];
			$this->plot = $sum_objt->GetPlot(&$form, 'day', $this->plots[gfetch]);			
		}
		
		$this->graph->title->Set($this->styles[title]);
		
		if (! empty($this->styles['subtitles'])){
			$this->graph->tabtitle->Set($this->styles['subtitles']);
			$this->graph->tabtitle->SetWidth(TABTITLE_WIDTHFULL);
		}
		
		if (! empty($this->styles['backgroundgradient']) && $this->styles['backgroundgradient'])
			$this->graph->SetBackgroundGradient('#FFFFFF','#CDDEFF:0.8',GRAD_HOR,BGRAD_PLOT);
		
		if (! empty($this->styles['rowcolor']) && $this->styles['rowcolor']){
			$this->graph->ygrid->SetFill(true,'#EFEFEF@0.5','#CDDEFF@0.5');
			$this->graph->xgrid->SetColor('gray@0.5');
			$this->graph->ygrid->SetColor('gray@0.5');
		}
		
		switch($this->styles[type]){
		case 'bar':
			
			if (! empty($this->styles['xlabelangle'])){
				$this->graph->xaxis->SetLabelAngle($this->styles['xlabelangle']);
				if ($this->styles['xlabelangle']<0)
					$this->graph->xaxis->SetLabelAlign('left');
			}
			if (! empty($this->styles['xlabelfont']))
				$this->graph->xaxis->SetFont($this->styles['xlabelfont']);
			else
				$this->graph->xaxis->SetFont(FF_VERA);				
			
			$this->graph->xaxis->SetTickLabels($this->plot['xdata']);
			$bplot = new BarPlot($this->plot['ydata']);
			$this->graph->Add($bplot);
			if ($form->FG_DEBUG>2){
				echo "X data: ";
				print_r($this->plot['xdata']);
				echo "\n Y data: ";
				print_r($this->plot['ydata']);
			}
			if ($form->FG_DEBUG>1)
				echo "Added Bar plot";
			break;
			
		case 'pie':
			$xdata = array();
			$ydata = array();
			$xkey = $tsum['x'];
			$ykey = $tsum['y'];
			while ($row = $res->fetchRow()){
				$xdata[] = $row[$xkey].' : '.$row[$ykey].' '.$tsum['ylabel'];
				$ydata[] = $row[$ykey];
			}
			
			if (! empty($tsum['xlabelfont']))
				$this->graph->xaxis->SetFont($tsum['xlabelfont']);
			else
				$this->graph->xaxis->SetFont(FF_VERA);				
			
			$pieplot = new PiePlot3D($ydata);
			$pieplot->ExplodeSlice(2);
			$pieplot->SetCenter(0.35);
			$pieplot->SetLegends(array_reverse($xdata));
			
			$this->graph->Add($pieplot);
			if ($form->FG_DEBUG>2){
				echo "X data: ";
				print_r($xdata);
				echo "\n Y data: ";
				print_r($ydata);
			}
			if ($form->FG_DEBUG>1)
				echo "Added Pie plot";
			break;
		case 'abar':
	/*		$this->graph->legend->SetColor('navy');
			$this->graph->legend->SetFillColor('gray@0.8');
			$this->graph->legend->SetLineWeight(1);
			//$this->graph->legend->SetFont(FF_ARIAL,FS_BOLD,8);
			$this->graph->legend->SetShadow('gray@0.4',3);
			$this->graph->legend->SetAbsPos(15,130,'right','bottom');*/
			//$this->graph->legend->SetFont(FF_VERA);
			
			$xdata = array();
			$ydata = array();
			$yleg =array(); //holds the labels for y axises
			$xkey = $tsum['x'];
			$x2key = $tsum['x2'];
			if (!empty($tsum['x2t']))
				$x2t=$tsum['x2t'];
			else
				$x2t=$x2key;
			$ykey = $tsum['y'];
			while ($row = $res->fetchRow()){
				// assume first order is by x-value
				if (empty($xdata) || (end($xdata) != $row[$xkey]))
					$xdata[] = $row[$xkey];
				// and assume second order is the x2 key..
				if (!isset($ydata[$row[$x2key]]))
					$ydata[$row[$x2key]]=array();
				
				end($xdata); // move pointer to end
				$ydata[$row[$x2key]][key($xdata)] = $row[$ykey];
				$yleg[$row[$x2key]] = $row[$x2t];
			}
			
			// Now, fill with zeroes all other vars..
			foreach($ydata as &$yd)
				foreach($xdata as $xk => $xv)
				if (!isset($yd[$xk]))
					$yd[$xk]=0;
				
			
			if (! empty($tsum['xlabelangle'])){
				$this->graph->xaxis->SetLabelAngle($tsum['xlabelangle']);
				if ($tsum['xlabelangle']<0)
					$this->graph->xaxis->SetLabelAlign('left');
			}
			if (! empty($tsum['xlabelfont']))
				$this->graph->xaxis->SetFont($tsum['xlabelfont']);
			else
				$this->graph->xaxis->SetFont(FF_VERA);
			$this->graph->xaxis->SetTickLabels($xdata);
			$accplots=array();
			
			$colors=array();
			$colors[]="yellow@0.3";
			$colors[]="purple@0.3";
			$colors[]="green@0.3";
			$colors[]="blue@0.3";
			$colors[]="red@0.3";

			$i=0;
			foreach($ydata as $yk => $ycol){
				$accplots[]= new BarPlot($ycol);
				end($accplots)->SetFillColor($colors[$i++]);
				if (!empty($yleg[$yk]))
					end($accplots)->SetLegend($yleg[$yk]);
				else
					end($accplots)->SetLegend(_("(none)"));
			}
			
			$bplot = new AccBarPlot($accplots);
			$this->graph->Add($bplot);
			if ($form->FG_DEBUG>2){
				echo "X data: ";
				print_r($xdata);
				echo "\n Y data: ";
				print_r($ydata);
			}

			if ($form->FG_DEBUG>1)
				echo "Added Bar plot";
			break;

		default:
			if ($form->FG_DEBUG>1)
			echo "Unknown graph type: ".$tsum['type'] . "\n";
		}
		
		if ($FG_DEBUG)
			echo "Stroke!";
		else
			$this->graph->Stroke();
		
		return true;
	}

};

?>