<?php
	namespace App\Controllers;

	use MF\Controller\Action;
	use MF\Model\Container;

	class AppController extends Action {
		public function validarAuth() {
			session_start();

			if (isset($_SESSION['id'])) return true;
			else {
				header('location: /');
				return false;
			}
		}
		
		public function recuperarInfoUsuario() {
			$usuario = Container::getModel('Usuario');
			$usuario->__set('id', $_SESSION['id']);

			$this->view->totalTweets = $usuario->getTotalTweets();
			$this->view->totalSeguindo = $usuario->getTotalSeguindo();
			$this->view->totalSeguidores = $usuario->getTotalSeguidores();
		}

		public function timeline() {
			if (!$this->validarAuth()) return;

			$tweet = Container::getModel('Tweet');

			$tweet->__set('id_usuario', $_SESSION['id']);
			$this->view->tweets = $tweet->getAll();

			$this->recuperarInfoUsuario();

			$this->render('timeline');
		}

		public function tweet() {
			if (!$this->validarAuth()) return;

			$tweet = Container::getModel('Tweet');

			$tweet->__set('tweet', $_POST['tweet']);
			$tweet->__set('id_usuario', $_SESSION['id']);

			$tweet->salvar();
			header('location: /timeline');
		}

		public function quemSeguir() {
			if (!$this->validarAuth()) return;

			$pesquisarPor = isset($_GET['pesquisarPor']) ? $_GET['pesquisarPor'] : '';
			$_SESSION['pesquisarPor'] = $pesquisarPor;

			$usuarios = array();

			if($pesquisarPor) {
				$usuario = Container::getModel('Usuario');

				$usuario->__set('nome', $pesquisarPor);
				$usuario->__set('id', $_SESSION['id']);

				$usuarios = $usuario->getAll();
			}

			$this->view->usuarios = $usuarios;
			$this->recuperarInfoUsuario();

			$this->render('quemSeguir');
		}

		public function acao() {
			if (!$this->validarAuth()) return;

			$acao = isset($_GET['acao']) ? $_GET['acao'] : '';
			$id_usuario_alvo = isset($_GET['id_usuario_alvo']) ? $_GET['id_usuario_alvo'] : '';

			$usuario = Container::getModel('Usuario');
			$usuario->__set('id', $_SESSION['id']);
			
			if ($acao == 'seguir') {
				$usuario->seguirUsuario($id_usuario_alvo);
			} elseif ($acao == 'deixar_seguir') {
				$usuario->deixarSeguirUsuario($id_usuario_alvo);
			}

			header('location: /quem_seguir?pesquisarPor=' . $_SESSION['pesquisarPor']);
		}
	}
?>