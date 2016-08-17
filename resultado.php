<?php 
// Alunos: Luiza Rabuski, Moisés Rodrigues Machado, Samantha da Rosa Machado

// Campos de entrada
	$gramatica = array("E -> E + T", "E -> T", "T -> T * F", "T -> F", "F -> P ^ F", "F -> P", "P -> ( E )", "P -> id");
	$terminais = array('+', '*', '^', '(', ')', 'id');
	$naoterminais = array("E", "T", "F", "P");
	$sentenca = array("id","+","id","$");

// passo 1: Determinar os primeiros e os últimos terminais relacionados com os não terminais da gramática
	$primeiros = getPrimeiros($gramatica, $terminais, $naoterminais);
	$ultimos = getUltimos($gramatica, $terminais, $naoterminais);
	echo 'PASSO 1';
	geraTabelaPasso1($primeiros, $ultimos);

// passo 2: Para determinar menor precedência (<), procurar na gramática, pares TNt (terminal seguido de não terminal) nos lados direitos das produções.
//          T < primeiros de NT
	$tabelaParaReconhecimento = [];
	$pares1 = getPares1($gramatica, $terminais, $naoterminais);
	echo '<br/>PASSO 2';
	$tabelaParaReconhecimento = geraTabelaPasso2($pares1, $primeiros, $tabelaParaReconhecimento);
	

// passo 3: Para determinar maior precedência (>), procurar na gramática, pares NtT (não terminal seguido de terminal) nos lados direitos das produções
//          Últimos Nt > T
	$pares2 = getPares2($gramatica, $terminais, $naoterminais);
	echo '<br/>PASSO 3';
	$tabelaParaReconhecimento = geraTabelaPasso3($pares2, $ultimos, $tabelaParaReconhecimento);

// passo 4: Para determinar = (igual precedência) procurar pares XaYbZ, onde Y é SENTENÇA VAZIA ou um Nt e X e Z são arbitrários. 
	$pares3 = getPares3($gramatica, $terminais, $naoterminais);
	echo '<br/>PASSO 4';
	$tabelaParaReconhecimento = geraTabelaPasso4($pares3, $tabelaParaReconhecimento);

// passo 5: $ é < (menor precedência) do que os primeiros do símbolo inicial
	echo '<br/>PASSO 5';
	$tabelaParaReconhecimento = geraTabelaPasso5($primeiros, $tabelaParaReconhecimento);

// passo 6: Os últimos do símbolo inicial são > (maior precedência) que $
	echo '<br/>PASSO 6';
	$tabelaParaReconhecimento = geraTabelaPasso6($ultimos, $tabelaParaReconhecimento);

// passo 7: Gerar tabela de simbolos para reconhecimento
	echo '<br/>TABELA';
	geraTabelaDeReconhecimento($tabelaParaReconhecimento, $terminais);

// passo 8: Reconhecimeno onde < e = empilham e > reduz
	echo '<br/>RECONHECIMENTO';
	$novaGramatica = gerarArraySimbolos($gramatica);
	reconhecimento($novaGramatica, $sentenca, $tabelaParaReconhecimento, $terminais);
?>

<?php 

	function getPares3($gramatica, $terminais, $naoterminais){
		$par = [];
		foreach ($gramatica as $key => $g) {
			$quebra = explode(' ', $g);
			$term = $quebra[0];
			foreach ($quebra as $k => $q) {
				if($k > 1){
					if(in_array($q, $terminais)){
						if(in_array($quebra[$k-1], $naoterminais)){
							if(in_array($quebra[$k-2], $terminais)){
								$par[$term] = $quebra[$k-2].$quebra[$k-1].$q;
							}
						}
					}
				}
			}
		}
		return $par;
	}

	function getPares2($gramatica, $terminais, $naoterminais){
		$par = [];
		foreach ($gramatica as $key => $g) {
			$quebra = explode(' ', $g);
			$term = $quebra[0];
			foreach ($quebra as $k => $q) {
				if($k > 1){
					if(in_array($q, $terminais)){
						if(in_array($quebra[$k-1], $naoterminais)){
					 		$par[] = $quebra[$k-1].$q;
					 	}
					}
				}
			}
		}
		return $par;	
	}

	function getPares1($gramatica, $terminais, $naoterminais){
		$par = [];
		foreach ($gramatica as $key => $g) {
			$quebra = explode(' ', $g);
			$term = $quebra[0];
			foreach ($quebra as $k => $q) {
				if($k > 1){
					if(in_array($q, $naoterminais)){
						if(in_array($quebra[$k-1], $terminais)){
							$par[] = $quebra[$k-1].$q;
						}
					}
				}
			}
		}
		return $par;	
	}

	function getPrimeiros($gramatica, $terminais, $naoterminais){
		$pri = [];
		$falta = [];
		foreach ($gramatica as $key => $g) {
			$quebra = explode(' ', $g);
			$term = $quebra[0];
			$teste = 1;
			foreach ($quebra as $k => $q) {
				if(in_array($q, $terminais) && $teste === 1){
					$pri[$term][] = $q;
					$teste = 0;
				}
			}
			if($teste === 1){
				$falta[] = $quebra;
			}
		}
		krsort($falta);
		foreach ($falta as $key => $fa) {
			foreach ($fa as $k => $f) {
				if($k>0){
					if($fa[$k-1] === '->'){
						$term = $fa[$k-2];
						$pri[$term] = array_merge($pri[$term], $pri[$f]);
					}
				}
			}
		}
		return $pri;	
	}

	function getUltimos($gramatica, $terminais, $naoterminais){
		$pri = [];
		$falta = [];
		foreach ($gramatica as $key => $g) {
			$quebra = explode(' ', $g);
			$term = $quebra[0];
			$teste = 1;
			krsort($quebra);
			foreach ($quebra as $k => $q) {
				if(in_array($q, $terminais) && $teste === 1){
					$pri[$term][] = $q;
					$teste = 0;
				}
			}
			if($teste === 1){
				$falta[] = $quebra;
			}
		}
		krsort($falta);
		foreach ($falta as $key => $fa) {
			foreach ($fa as $k => $f) {
				if($k>0){
					if($fa[$k-1] === '->'){
						$term = $fa[$k-2];
						$pri[$term] = array_merge($pri[$term], $pri[$f]);
					}
				}
			}
		}
		return $pri;
	}

	function geraTabelaPasso1($primeiros, $ultimos){
		$html = '<table cellpadding="10" cellspacing="1" border="1">';
		$html .= '<tr><th></th><th>Primeiros</th><th>Ultimos</th>';
        $html .= '</tr>';
        foreach ($primeiros as $key => $p) {
        	$html .= '<tr align="center">';
			$html .= '<td>' . $key . '</td>'; 
			$html .= '<td>' . implode(",", $p) . '</td>';
			$html .= '<td>' . implode(",", $ultimos[$key]) . '</td>';
			$html .= '</tr>';
        }
        $html .= '</table>';
        echo $html;
	}

	function geraTabelaPasso2($pares, $primeiros, $tabelaParaReconhecimento){
		$a = [];
		$table = [];
		foreach ($pares as $key => $par) {
			$a = str_split($par);
			foreach ($primeiros as $k => $p) {
				if($a[1] === $k){
					$b = $a[1];
					$table[$par] = $p;
				}
        	}
		}
        $html = '<table cellpadding="10" border="1">';
        foreach ($table as $key => $tab) {
        	$a = str_split($key);
        	$html .= '<tr><th>'. $key . '</th>';
        	foreach ($tab as $k => $t) {
        		$html .='<td>'. $a[0] . ' < ' . $t . '</td>';
				$tabelaParaReconhecimento[$a[0]][$t] = "<";
        	}
        	$html .= '</tr>';
        }
        $html .= '</table>';
        echo $html;
		return $tabelaParaReconhecimento;
	}

	function geraTabelaPasso3($pares2, $ultimos, $tabelaParaReconhecimento){
		$a = [];
		$table = [];
		foreach ($pares2 as $key => $par) {
			$a = str_split($par);
			foreach ($ultimos as $k => $p) {
				if($a[0] === $k){
					$b = $a[0];
					$table[$par] = $p;
				}
        	}
		}
        $html = '<table cellpadding="10" border="1">';
        foreach ($table as $key => $tab) {
        	$a = str_split($key);
        	$html .= '<tr><th>'. $key . '</th>';
        	foreach ($tab as $k => $t) {
        		$html .='<td>'. $t . ' > ' . $a[1] . '</td>';
				$tabelaParaReconhecimento[$t][$a[1]] = ">";
        	}
        	$html .= '</tr>';
        }
        $html .= '</table>';
        echo $html;
		return $tabelaParaReconhecimento;
	}

	function geraTabelaPasso4($pares3, $tabelaParaReconhecimento){
		$html = '<table cellpadding="10" cellspacing="1" border="1">';
        foreach ($pares3 as $key => $p) {
        	$a = str_split($p);
        	$html .= '<tr align="center">';
        	$html .='<td>'. $a[0]. ' = ' . $a[2] . '</td>'; 
			$tabelaParaReconhecimento[$a[0]][$a[2]] = "=";
			$html .= '</tr>';
        }
        $html .= '</table>';
        echo $html;
		return $tabelaParaReconhecimento;
	}

	function geraTabelaPasso5($primeiros, $tabelaParaReconhecimento){
		$html = '<table cellpadding="10" cellspacing="1" border="1">';
        foreach ($primeiros['E'] as $key => $p) {
        	$html .= '<tr align="center">';
        	$html .='<td> $ < ' . $p . '</td>'; 
			$tabelaParaReconhecimento['$'][$p] = "<";
			$html .= '</tr>';
        }
        $html .= '</table>';
        echo $html;
		return $tabelaParaReconhecimento;
	}

	function geraTabelaPasso6($ultimos,$tabelaParaReconhecimento){
		$html = '<table cellpadding="10" cellspacing="1" border="1">';
        foreach ($ultimos['E'] as $key => $p) {
        	$html .= '<tr align="center">';
        	$html .='<td>' . $p . ' > $ </td>'; 
			$tabelaParaReconhecimento[$p]['$'] = ">";
			$html .= '</tr>';
        }
        $html .= '</table>';
        echo $html;
		return $tabelaParaReconhecimento;
	}
	
	function geraTabelaDeReconhecimento($tabelaParaReconhecimento, $terminais){
		$terminais[] = '$'; 
		$html = '<table cellpadding="10" cellspacing="1" border="1">';
		$html .= '<tr><th></th>';
		foreach ($terminais as $terminal){
			$html .= '<th>'. $terminal . '</th>';
		}	
		$html .= '</tr>';
		
		foreach ($terminais as $terminal){
			$html .= '<tr align="center"><th>'. $terminal . '</th>';
			foreach($terminais as $terminal2){
				$html .= '<td>'. @$tabelaParaReconhecimento[$terminal][$terminal2] . '</td>';
			}
		}
		
        $html .= '</table>';
        echo $html;
	}
		
	function gerarArraySimbolos($gramatica){
		$gramaticas = [];
		$letras = "";
		foreach($gramatica as $sentenca){
			$simbolos = explode(' ',$sentenca);
			foreach($simbolos as $key => $simbolo){
				if($key > 1){
					$letras[] = $simbolo;
				}
			}
			$gramaticas[$simbolos[0]][] = $letras;
			$letras = "";
		}
		return $gramaticas;
	}
	
	function reconhecimento($gramatica, $entrada, $tabelaParaReconhecimento, $terminais){
		$html = '<table cellpadding="10" cellspacing="1" border="1">';
		$html .= '<tr align="center"><th>PASSO</th><th>PILHA</th><th>RELACAO</th><th>ENTRADA</th><th>HANDLE</th><th>ACAO</th></tr>';
		
		$passo = 1;
		$handle = 0;
		$naoReconhece = 0;
		$pilha = ["$"];
		$pilhaOperadores = ["$"];
		$terminais[] = "$";
		$aceitaSentença = "ACEITA";
		$mostraEntrada = $entrada;
		
		while($simboloEntrada = current($entrada)){
			$chaveEntrada = key($entrada);
			$pilha = array_values($pilha);
			end($pilha);
			end($pilhaOperadores);
			$chaveOperadores = key($pilhaOperadores);
			$chavePilha = key($pilha);
			
			if(in_array($pilha[$chavePilha],$terminais)){
				$operador = $pilha[$chavePilha];
			}else{
				$operador = $pilhaOperadores[$chaveOperadores];
			}
			@$reconhecimento = $tabelaParaReconhecimento[$operador][$simboloEntrada];		
			if($simboloEntrada == "$" && $pilha[$chavePilha] == "$"){
				break;
			}
			if(!empty($reconhecimento)){
				if ($reconhecimento == "<" || $reconhecimento == "="){
					
					$html .= '<tr align="center"><td>' . $passo . '</td><td>';
					foreach($pilha as $p){
						$html .= $p;
					}
					$html .= '</td><td></td><td>';
					foreach($mostraEntrada as $me){
						$html .= $me;
					}
					$html = '</td><td></td><td>EMPILHA</td></tr>';
					
					$passo++;
					$pilha[] = $simboloEntrada;
					$pilhaOperadores[] = $simboloEntrada;
					next($entrada);
					unset($mostraEntrada[$chaveEntrada]);					
				}elseif ($reconhecimento == ">"){
					foreach($gramatica as $key => $simbolos){
						foreach($simbolos as $letras){
							if(in_array($operador,$letras)){
								$handle = 1;
								
								$html .= '<tr align="center"><td>' . $passo . '</td><td>';
								foreach($pilha as $p){
									$html .= $p;
								}								
								$html .= '</td><td></td><td>';
								foreach($mostraEntrada as $me){
									$html .= $me;
								}								
								$html = '</td><td>'; 
								foreach($letras as $l){
									$html .= $l;
								}
								
								$html .= '</td><td>REDUZ ' . $key . ' -> ';
								foreach($letras as $l){
									$html .= $l;
								}
								$html .= '</td></tr>';
								
								if($simboloEntrada == "$"){
									$tamanhoSimbolos = count($letras)-1;
									if(@!($letras[0] == $pilha[$chavePilha-1]) && @!($letras[$tamanhoSimbolos] == $pilha[$chavePilha+1])){
										$naoReconhece = 1;
										$aceitaSentença = "NAO ACEITA";
									}else{																			
										for($x = $tamnhoSimbolo; $x >= 0 ;$x--){
											unset($pilha[$chavePilha-$x]);
										}
									}	
								}else{
									unset($pilhaOperadores[$chaveOperadores]);
									$pilhaOperadores[$chavePilha] = $key;
								}
								$passo++;
							}
						}						
						if($handle == 1){
							break;
						}
						if($naoReconhece == 1){
							break;
						}
					}
					if($handle == 0){
						$aceitaSentença = "NAO ACEITA";
						break;
					}
					$handle = 0;								
				}
			}else{
				$aceitaSentença = "NAO ACEITA";
				break;
			}
		}
		$html .= '</table>';
        echo $html . '<p>ACEITA SENTENCA ?        ' . $aceitaSentença;
	}
?>