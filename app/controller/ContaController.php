<?php

class ContaController{

    //carrega o dashboard da conta ao fazer login
    public static function index(){
        $pos = strpos($_SESSION['userName']," ");
        $nome = substr($_SESSION['userName'], 0,$pos);
        //carrega um objeto twig configurado
        $twig = TwigConfig::loader();


        $conta = new Conta($_SESSION['userId']);
        
        
        //carrega o conteúdo da view e modfica as variáves
        $conteudo = $twig->render('dashboard.html', ['nome'=>$nome, 'saldo'=>$conta->getSaldo()]);
        $menu = $twig->render('sideMenu.html');
        
        //adiciona o conteúdo da página e o menu no template
        echo $twig->render('template.html', ['titulo'=> 'Minha Conta - Banco Horizon',
                                             'conteudo'=>$conteudo,
                                             'menu'=>$menu,
                                             'css'=>'/BancoHorizonClient/public/dashboard.css',
                                             'script'=>'/BancoHorizonClient/public/js/sideMenu.js']);
    }



    public static function FunctionName(Type $var = null)
    {
        header("location: /BancoHorizonclient");
    }


    //===================================== AJAX RESPONSE ========================================================
    public static function ajax(){
       
    }


    //===================================== PAGAMENTOS ========================================================
    public static function pagamento(){  
        $conta = new Conta($_SESSION['userId']);
        $boleto = new Boleto();
        //carrega um objeto twig configurado
        $twig = TwigConfig::loader();

        //responde ao ajax que pesquisa pelo boleto
        if(isset($_GET['numBoleto'])){
            $dadosBoleto = $boleto->procuraBoleto($_GET['numBoleto']);
            $boleto->calcularBoleto();
            echo($boleto->toJson());

            
        }else{
            //pagamento do boleto
            if (isset($_POST['codigo']) && isset($_POST['dataPagamento']) && isset($_POST['valorTotal'])) {
                $boleto->procuraBoleto($_POST['codigo']);
                $boleto->calcularBoleto();
                var_dump($boleto);
                $resultado = $boleto->pagarBoleto($conta);
                

                if ($resultado) {
                    $extrato = new Extrato();

                    $extrato->pagamento();
                    //carrega o conteúdo da view e modfica as variáves
                    $conteudo = $twig->render('pagamento/sucesso.html', ['saldo'=>$conta->getSaldo(),
                    "dataAtual"=> date("Y-m-d")]);
                } else {
                    header("location: erro");
                }
            } else {
                //falaha no pagamento do boleto
                $conteudo = $twig->render('pagamento/pagamento.html', ['saldo'=>$conta->getSaldo(),
                                                                        "dataAtual"=> date("Y-m-d")]);
            }   
                
                //adiciona o conteúdo da página e o menu no template
                $menu = $twig->render('sideMenu.html');
                echo $twig->render('template.html', ['titulo'=> 'Pagamentos - Banco Horizon',
                                                    'conteudo'=>$conteudo,
                                                    'menu'=>$menu,
                                                    'css'=>'/BancoHorizonClient/public/dashboard.css',
                                                    'script'=>'/BancoHorizonClient/public/js/sideMenu.js']);
            
        }
        
    }

    // ================================== TRANSFERÊNCIAS ====================================================
    public static function transferencia(){
        //carrega um objeto twig configurado
        $twig = TwigConfig::loader();

        $conta = new Conta($_SESSION['userId']);
        $transf = new Transferencia();
        
        
        
        //checa se uma transferência foi solicitada
        if(isset($_POST['valor']) && isset($_POST['agencia']) && isset($_POST['conta']) && isset($_POST['banco']) && isset($_POST['tipoConta'])){
            //chama o método transferir ao solicitar essa operação
            $res = $transf->transferir($_POST, $conta);

            if($res){
                //registro no extrato
                $extrato = new Extrato();

                $extrato->transferencia($transf);
                
                //renderiza a view de sucesso na transferencia
                $conteudo = $twig->render('transferencia/sucesso.html', ['transf'=> $transf->getValorTransf(), 'saldo'=>$conta->getSaldo()]);
                unset($POST);
            }else{
                //transferência mal sucedida
                $conteudo = $twig->render('transferencia/erro.html', ['saldo'=>"ERRO"]);
            }
        }else{
            //carrega o conteúdo da view e modfica as variáves
            $conteudo = $twig->render('transferencia/transferencia.html', ['saldo'=>$conta->getSaldo()]);
           
        }

        
        
        //adiciona o conteúdo da página e o menu no template
        $menu = $twig->render('sideMenu.html');
        echo $twig->render('template.html', ['titulo'=> 'Transferência - Banco Horizon',
                                         'conteudo'=>$conteudo,
                                         'menu'=>$menu,
                                         'css'=>'/BancoHorizonClient/public/dashboard.css',
                                         'script'=>'/BancoHorizonClient/public/js/sideMenu.js']);
        
    }

   

    //=============================================== BOLETOS ================================================
    public static function boleto(Type $var = null)
    {
        //carrega um objeto twig configurado
        $twig = TwigConfig::loader();
        $boleto = new Boleto();
        
        if (isset($_POST['valorBoleto']) && isset($_POST['dataVencimento'])) {
            $resultado = $boleto->gerarBoleto($_POST['valorBoleto'], $_POST['dataVencimento']);
            //carrega o conteúdo da view e modfica as variáves
            if($resultado){
                $conteudo = $twig->render('boleto/sucesso.html', ["boletoVal"=> $boleto->getValor(),
                                                                  "numBoleto"=> $boleto->getNumBoleto()]);
            }else{
                $conteudo = $twig->render('boleto/erro.html', ["dataAtual"=> date("Y-m-d")]);
            }
        }else{
            $conteudo = $twig->render('boleto/boleto.html', ["dataAtual"=> date("Y-m-d")]);
        }
       
        
        
        
        
        //adiciona o conteúdo da página e o menu no template
        $menu = $twig->render('sideMenu.html');
        echo $twig->render('template.html', ['titulo'=> 'Gerar Boleto - Banco Horizon',
                                             'conteudo'=>$conteudo,
                                             'menu'=>$menu,
                                             'css'=>'/BancoHorizonClient/public/dashboard.css',
                                             'script'=>'/BancoHorizonClient/public/js/sideMenu.js']);
    }

    //================================================ EXTRATO ============================================
    public static function extrato(Type $var = null)
    {
        $pos = strpos($_SESSION['userName']," ");
        $nome = substr($_SESSION['userName'], 0,$pos);
        //carrega um objeto twig configurado
        $twig = TwigConfig::loader();


        $conta = new Conta($_SESSION['userId']);
        $extrato = new Extrato();
        $dados = $extrato->verExtrato();

        //carrega o conteúdo da view e modfica as variáves
        $conteudo = $twig->render('extrato.html', ['saldo'=>$conta->getSaldo(), 'queryResult'=> $dados]);
        
        
        //adiciona o conteúdo da página e o menu no template
        $menu = $twig->render('sideMenu.html');
        echo $twig->render('template.html', ['titulo'=> 'Extrato - Banco Horizon',
                                             'conteudo'=>$conteudo,
                                             'menu'=>$menu,
                                             'css'=>'/BancoHorizonClient/public/dashboard.css',
                                             'script'=>'/BancoHorizonClient/public/js/sideMenu.js']);
    }

    //============================================= CARTÕES ==================================================
    public static function cartao(Type $var = null)
    {
        $pos = strpos($_SESSION['userName']," ");
        $nome = substr($_SESSION['userName'], 0,$pos);
        //carrega um objeto twig configurado
        $twig = TwigConfig::loader();


        $conta = new Conta($_SESSION['userId']);

        
        
        //carrega o conteúdo da view e modfica as variáves
        $conteudo = $twig->render('cartao.html', ['saldo'=>$conta->getSaldo()]);
        
        
        //adiciona o conteúdo da página e o menu no template
        $menu = $twig->render('sideMenu.html');
        echo $twig->render('template.html', ['titulo'=> 'Meus Cartões - Banco Horizon',
                                             'conteudo'=>$conteudo,
                                             'menu'=>$menu,
                                             'css'=>'/BancoHorizonClient/public/dashboard.css',
                                             'script'=>'/BancoHorizonClient/public/js/sideMenu.js']);
    }

    //
   /* public function FunctionName(Type $var = null)
    {
        # code...
    }*/
}