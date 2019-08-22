<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

$mdfe = new \NFePHP\MDFe\Make();
$mdfeTools = new \NFePHP\MDFe\Tools("config.json");
$dhEmi = date("Y-m-d\TH:i:sP");
$numeroMDFe = "1";

$cUF = '35';
$ano = '19';
$tpAmb = '2';
$tpTransp = '';
$tpEmit = '2';
$modal = '1';
$procEmi = '0';
$verProc = '3.00';
$ufIni = 'SP';
$ufFim = 'SP';
$dhIniViagem = '2019-08-22T10:24:00-03:00';
$mes = '08';
$cnpj = '01889578000103';
$numIE = '114911697112';
$xNome = 'ILHA COMERCIO E TRANSPORTES LTDA';
$xFant = 'ILHA COMERCIO';
$mod = '58';
$serie = '0';
$numero = $numeroMDFe;
$tpEmis = '1'; // 1 - normal | 2 - contingência
$codigo = '00005390';

$chave = $mdfe->montaChave($cUF, $ano, $mes, $cnpj, $mod, $serie, $numero, $tpEmis, $codigo);

$resp = $mdfe->taginfMDFe($chave, $versao = '3.00');
$cDV = substr($chave, -1);

//$tpTransp = '1',
$resp = $mdfe->tagide(
        $cUF,
        $tpAmb,
        $tpEmit,
        $tpTransp,
        $mod,
        $serie,
        $numeroMDFe,
        $codigo,
        $cDV,
        $modal,
        $dhEmi,
        $tpEmis,
        $procEmi,
        $verProc,
        $ufIni,
        $ufFim,
        $dhIniViagem
    );

$resp = $mdfe->tagInfMunCarrega(
    $cMunCarrega = '3550308',
    $xMunCarrega = 'SAO PAULO'
);

/*
$resp = $mdfe->tagInfPercurso($ufPer = 'GO');
*/

$resp = $mdfe->tagemit(
        $cnpj,
        $numIE,
        $xNome,
        $xFant
    );

$resp = $mdfe->tagenderEmit(
        $xLgr = 'R. ONTINENTINO',
        $nro = '1313',
        $xCpl = '',
        $xBairro = 'CAICARAS',
        $cMun = '3550308',
        $xMun = 'Sao Paulo',
        $cep = '05201140',
        $siglaUF = 'SP',
        $fone = '31988998899',
        $email = 'email@hotmail.com'
    );

$resp = $mdfe->tagInfMunDescarga(
        $nItem = 0,
        $cMunDescarga = '3550308',
        $xMunDescarga = 'SAO PAULO'
    );

/*
$resp = $mdfe->tagInfCTe(
        $nItem = 0,
        $chCTe = '31171009204054000143570010000015441090704345',
        $segCodBarra = ''
    );
*/
$resp = $mdfe->tagInfNFe(
        $nItem = 0,
        $chNFe = '52190802773950000184550050002097341451862169',
        $segCodBarra = ''
    );

/*    
$resp = $mdfe->tagSeg(
        $nApol = '1321321321',
        $nAver = $numeroMDFe
    );
$resp = $mdfe->tagInfResp(
        $respSeg = '1',
        $CNPJ = '',
        $CPF = ''
    );

$resp = $mdfe->tagInfSeg(
        $xSeg = 'SOMPRO',
        $CNPJ = '11095658000140'
    );
*/

$resp = $mdfe->tagTot(
        $qCTe = '',
        $qNFe = '1',
        $qMDFe = '',
        $vCarga = '157620.00',
        $cUnid = '01',
        $qCarga = '2323.0000'
    );

/*
$resp = $mdfe->tagautXML(
        $cnpj = '',
        $cpf = '09835787667'
    );
*/

$resp = $mdfe->taginfAdic(
        $infAdFisco = 'Inf. Fisco',
        $infCpl = 'Inf. Complementar do contribuinte'
    );

$resp = $mdfe->tagInfModal($versaoModal = '3.00');

$resp = $mdfe->tagRodo(
        $rntrc = '10167059'
    );
/*
$resp = $mdfe->tagInfContratante(
        $CPF = '09835783624'
    );
*/
$resp = $mdfe->tagCondutor(
        $xNome = 'fjaklsdjksdjf faksdj',
        $cpf = '54808987104'
    );

$resp = $mdfe->tagVeicTracao(
        $cInt = '', // Código Interno do Veículo
        $placa = 'ABC1234', // Placa do veículo
        $RENAVAM = '00172788277',
        $tara = '10000',
        $capKG = '500',
        $capM3 = '60',
        $tpRod = '06',
        $tpCar = '02',
        $UF = 'SP',
        $propRNTRC = ''
    );

//gera xml
$resp = $mdfe->montaMDFe();
if ($resp) {
    $xml = $mdfe->getXML();
} else {
    header('Content-type: text/html; charset=UTF-8');
    foreach ($mdfe->erros as $err) {
        echo 'tag: &lt;' . $err['tag'] . '&gt; ---- ' . $err['desc'] . '<br>';
    }
}


//assina
$xmlAssinado = $mdfeTools->assina($xml);

//mostra exemplo do xml
$domxml = new DOMDocument('1.0');
$domxml->preserveWhiteSpace = false;
$domxml->formatOutput = true;
$domxml->loadXML($xmlAssinado);
echo '<textarea cols="300" rows="150">';
echo $domxml->saveXML();
echo "</textarea>";
//file_put_contents($_SERVER['DOCUMENT_ROOT']."/modulos/fiscalMdfe/xml/gerado.xml", $domxml->saveXML());

//valida
$msg='';
if (! $mdfeTools->validarXml($xmlAssinado) || sizeof($mdfeTools->errors)) {
    $msg .= "<h3>Algum erro ocorreu.... </h3>";
    foreach ($mdfeTools->errors as $erro) {
        if (is_array($erro)) {
            foreach ($erro as $err) {
                $msg .= "$err <br>";
            }
        } else {
            $msg .= "$erro <br>";
        }
    }
    throw new Exception($msg,0);
}

//envia
$aRetorno = array();
$retorno = $mdfeTools->sefazEnviaLote(
    $xmlAssinado,
    $tpAmb,
    $idLote = '',
    $aRetorno
);
echo '<pre>';
var_dump($aRetorno);
var_dump($chave);
echo '</pre>';


//temp
//$aRetorno['nRec'] = "359000007095762";

//consulta recibo
if(!empty($aRetorno['nRec'])){
    $aResposta = array();
    $recibo = $aRetorno['nRec'];
    $retorno = $mdfeTools->sefazConsultaRecibo($recibo, $tpAmb, $aResposta);
    echo '<pre>';
    echo htmlspecialchars($mdfeTools->soapDebug);
    print_r($aResposta);
    echo "</pre>";
}