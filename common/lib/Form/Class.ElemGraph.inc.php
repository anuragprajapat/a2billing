<?php
require_once(DIR_COMMON."jpgraph_lib/jpgraph.php");

/** This class collects data from other ElemBase objects through
    their RenderSpecial().
   */
abstract class DataObj{
	public $code;
	public function DataObj($co){
		$this->code=$co;
	}
	abstract function debug($str);
	
};

/** Intermediate class for data that only has 2 dimensions */
abstract class DataObjXY extends DataObj{
	abstract function PlotXY($x,$y);
};

/** Debug version of parent, dumps the data */
class DataObjXY_d extends DataObjXY {
	public function PlotXY($x,$y){
		echo "x=$x, y=$y <br>\n";
	}
	public function debug($str){
		echo "$str<br>\n";
	}
};

class DataObjXYp extends DataObjXY {
	public $xdata=array();
	public $ydata=array();
	
	public function PlotXY($x,$y){
		$this->xdata[]=$x;
		$this->ydata[]=$y;
	}
	public function debug($str){
	}
	
	public function Add_YtoX($separator = " : ", $end_x = ''){
		for ($i=0 ; $i < count($this->xdata); $i++){
			$this->xdata[$i] .= $separator . $this->ydata[$i] . $end_x;
		}
	}
};

/** A view that renders itself into a graph.
   This view will call some other view of the form, in order to fetch
   the data from it (using its RenderSpecial).
*/
class GraphView extends FormView {
	public $view;
	public $code;
	public $params;
	
	function GraphView($vi,$co,$pa){
		$this->view=$vi;
		$this->code=$co;
		$this->params=$pa;
	}
	
	public function RenderHeaderGraph (&$form, &$robj){
	}
	
	
	public function RenderGraph (&$form, &$robj){
		// For debugging purposes
		$data = new DataObjXYp($this->code);
		print_r ($data);
	}
	
	public function RenderSpecial($rmode,&$form, &$robj){
		if ($rmode=='create-graph'){
			$this -> RenderHeaderGraph($form, $robj);
			$this -> RenderHeadSpecial($form, $robj);
		}
		elseif ($rmode=='graph'){
			$this -> RenderGraph($form, $robj);
		}
	}
	
	public function RenderHeadSpecial(&$form, &$robj){
		
		//print_r ($this->params);
		if (!$this->params['setframe'])
			$robj->SetFrame(false);
		
		if (! empty($this->params['title']))
			$robj->title->Set($this->params['title']);
		
		if (! empty($this->params['subtitles'])){
			$robj->tabtitle->Set($this->params['subtitles']);
			$robj->tabtitle->SetWidth(TABTITLE_WIDTHFULL);
		}
		
		if ($this->params['backgroundgradient'])
			$robj->SetBackgroundGradient('#FFFFFF','#CDDEFF:1.1',GRAD_HOR,BGRAD_PLOT);
		
		if ($this->params['rowcolor']){
			$robj->ygrid->SetFill(true,'#EFEFEF@0.5','#CDDEFF@0.5');
			$robj->xgrid->SetColor('gray@0.5');
			$robj->ygrid->SetColor('gray@0.5');
		}
	}

	/** For debugging purposes, this function simulates the 
	  graph procedure but only renders the results into html text */
	function Render(&$form){
		if(!$form->FG_DEBUG)
			return true;
		?>
	<div class="debug">
	Here we are: debugging FormDataView
	<br>
		<?php
			$graph=null;
			$this->RenderSpecial('create-graph',$form,$graph);
			if ($graph instanceof Graph)
				echo "Created a graph object <br>\n";
			unset($graph);
			echo "Using view ".$this->view.", code=".$this->code." <br>\n";
			if (!isset($form->views[$this->view])){
				echo "View doesn't exist!!\n";
				echo "</div>";
				return false;
			}
		?>
		</div>
		<div class="debug">
		<?php
			$dobj=new DataObjXY_d($this->code);
			$form->views[$this->view]->RenderSpecial('get-data',$form,$dobj);
		?>
		</div>
	<?php
	}

/*	public $sums = array();
	public $plots = array();
	public $styles = array();
	public $graphtype = null;
	protected $graph = null;
	
/ -*
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
			$this->graph->legend->SetAbsPos(15,130,'right','bottom');* /
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
*/

};

class LineView extends GraphView {

	public function RenderHeaderGraph (&$form, &$robj){
		
		require_once(DIR_COMMON."jpgraph_lib/jpgraph_line.php");
		
		$robj = new Graph($this->params['width'],$this->params['height'],"auto");
		$robj->SetScale("textlin");
		$robj->yaxis->scale->SetGrace(3);
		$robj->SetMargin(40,40,45,90);
		
	}
	
	public function RenderGraph (&$form, &$robj){
		
		$data = new DataObjXYp($this->code);
		$form->views[$this->view]->RenderSpecial('get-data',$form,$data);
		
		if (! empty($this->params['xlabelangle'])){
			$robj->xaxis->SetLabelAngle($this->params['xlabelangle']);
			if ($this->params['xlabelangle']<0)
				$robj->xaxis->SetLabelAlign('left');
		}
		if (! empty($this->params['xlabelfont']))
			$robj->xaxis->SetFont($this->params['xlabelfont']);
		else
			$robj->xaxis->SetFont(FF_VERA);
		
		$robj->xaxis->SetTickLabels($data->xdata);
		$lineplot = new LinePlot($data->ydata);
		$lineplot->SetFillColor('gray@0.3');
		$lineplot ->SetColor("blue");
		$robj->Add($lineplot);	
	}

};

class BarView extends GraphView {

	public function RenderHeaderGraph (&$form, &$robj){
		
		require_once(DIR_COMMON."jpgraph_lib/jpgraph_bar.php");
		
		$robj = new Graph($this->params['width'],$this->params['height'],"auto");
		$robj->SetScale("textlin");
		$robj->yaxis->scale->SetGrace(3);
		$robj->SetMargin(40,40,45,90);
	}
	
	public function RenderGraph (&$form, &$robj){
		
		$data = new DataObjXYp($this->code);
		$form->views[$this->view]->RenderSpecial('get-data',$form,$data);
		
		if (! empty($this->params['xlabelangle'])){
			$robj->xaxis->SetLabelAngle($this->params['xlabelangle']);
			if ($this->params['xlabelangle']<0)
				$robj->xaxis->SetLabelAlign('left');
		}
		if (! empty($this->params['xlabelfont']))
			$robj->xaxis->SetFont($this->params['xlabelfont']);
		else
			$robj->xaxis->SetFont(FF_VERA);
		
		$robj->xaxis->SetTickLabels($data->xdata);
		$bplot = new BarPlot($data->ydata);
		$robj->Add($bplot);
	}

};


class PieView extends GraphView {

	public function RenderHeaderGraph (&$form, &$robj){
		
		require_once(DIR_COMMON."jpgraph_lib/jpgraph_pie.php");
		require_once(DIR_COMMON."jpgraph_lib/jpgraph_pie3d.php");
		
		$robj = new PieGraph($this->params['width'],$this->params['height'],"auto");
		$robj->SetScale("textlin");
		$robj->yaxis->scale->SetGrace(3);
		
	}
	
	public function RenderGraph (&$form, &$robj){
		
		$data = new DataObjXYp($this->code);
		$form->views[$this->view]->RenderSpecial('get-data',$form,$data);
		$data->Add_YtoX(" : ", ' seconds');
		
		/*while ($row = $res->fetchRow()){
			$xdata[] = $row[$xkey].' : '.$row[$ykey].' '.$tsum['ylabel'];
			$ydata[] = $row[$ykey];
		}
		
		if (! empty($tsum['xlabelfont']))
			$robj->xaxis->SetFont($tsum['xlabelfont']);
		else
			$robj->xaxis->SetFont(FF_VERA);				
		*/
		$pieplot = new PiePlot3D($data->ydata);
		$pieplot->ExplodeSlice(2);
		$pieplot->SetCenter(0.35);
		$pieplot->SetLegends(array_reverse($data->xdata));
		
		$robj->Add($pieplot);
			
	}
};
