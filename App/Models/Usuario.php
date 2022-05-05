<?php
	namespace App\Models;
	use MF\Model\Model;

	class Usuario extends Model {
		private $id;
		private $nome;
		private $email;
		private $senha;

		public function __get($atributo) {
			return $this->$atributo; 
		}

		public function __set($atributo, $valor) {
			$this->$atributo = $valor;
		}

		public function validarCadastro() {
			if(strlen($this->__get('nome')) < 3 || strlen($this->__get('email')) < 3 || strlen($this->__get('senha')) < 4) return false;
			
			return true;
		}

		public function getUsuarioPorEmail() {
			$query = 'SELECT nome, email FROM usuarios WHERE email = :email';

			$pdoStatement = $this->db->prepare($query);
			$pdoStatement->bindValue(':email', $this->__get('email'));
			$pdoStatement->execute();

			return $pdoStatement->fetchAll(\PDO::FETCH_ASSOC);
		}

		public function salvar() {
			$query = 'INSERT INTO usuarios(nome, email, senha) VALUES(:nome, :email, :senha)';			
			$pdoStatement = $this->db->prepare($query);

			$pdoStatement->bindValue(':nome', $this->__get('nome'));
			$pdoStatement->bindValue(':email', $this->__get('email'));
			$pdoStatement->bindValue(':senha', $this->__get('senha')); // md5() -> has 32 cars, criptografado.

			$pdoStatement->execute();
		}

		public function autenticar() {
			$query = 'SELECT id, nome, email FROM usuarios WHERE email = :email AND senha = :senha';
			$pdoStatement = $this->db->prepare($query);

			$pdoStatement->bindValue(':email', $this->__get('email'));
			$pdoStatement->bindValue(':senha', $this->__get('senha'));

			$pdoStatement->execute();
			$usuario = $pdoStatement->fetch(\PDO::FETCH_ASSOC);

			if(is_array($usuario)) {
				$this->__set('id', $usuario['id']);
				$this->__set('nome', $usuario['nome']);
			}
		}

		public function getAll() {
			$query = '
				SELECT
					usuarios.id, usuarios.nome,
					(
						SELECT
							count(*)
						FROM
							usuarios_seguidores
						WHERE
							usuarios_seguidores.id_usuario = :id AND usuarios_seguidores.id_usuario_seguindo = usuarios.id
					) AS seguindo_sn
				FROM
					usuarios
				WHERE
					usuarios.nome LIKE :nome AND usuarios.id != :id
			';

			$pdoStatement = $this->db->prepare($query);
			$pdoStatement->bindValue(':nome', '%' . $this->__get('nome') . '%');
			$pdoStatement->bindValue(':id', $this->__get('id'));

			$pdoStatement->execute();

			return $pdoStatement->fetchAll(\PDO::FETCH_ASSOC);
		}

		public function seguirUsuario($id_usuario_alvo) {
			$query = 'INSERT INTO usuarios_seguidores(id_usuario, id_usuario_seguindo) VALUES(:id_usuario, :id_usuario_seguindo)';

			$pdoStatement = $this->db->prepare($query);

			$pdoStatement->bindValue(':id_usuario', $this->__get('id'));
			$pdoStatement->bindValue(':id_usuario_seguindo', $id_usuario_alvo);

			$pdoStatement->execute();
		}

		public function deixarSeguirUsuario($id_usuario_alvo) {
			$query = 'DELETE FROM usuarios_seguidores WHERE id_usuario = :id_usuario AND id_usuario_seguindo = :id_usuario_seguindo';

			$pdoStatement = $this->db->prepare($query);

			$pdoStatement->bindValue(':id_usuario', $this->__get('id'));
			$pdoStatement->bindValue(':id_usuario_seguindo', $id_usuario_alvo);

			$pdoStatement->execute();
		}

		public function getTotalTweets() {
			$query = 'SELECT COUNT(*) AS total_tweets FROM tweets WHERE id_usuario = :id_usuario';

			$pdoStatement = $this->db->prepare($query);

			$pdoStatement->bindValue(':id_usuario', $this->__get('id'));
			$pdoStatement->execute();

			return $pdoStatement->fetch(\PDO::FETCH_ASSOC)['total_tweets'];
		}

		public function getTotalSeguindo() {
			$query = 'SELECT COUNT(*) AS total_seguindo FROM usuarios_seguidores WHERE id_usuario = :id_usuario';
			
			$pdoStatement = $this->db->prepare($query);
			
			$pdoStatement->bindValue(':id_usuario', $this->__get('id'));
			$pdoStatement->execute();
			
			return $pdoStatement->fetch(\PDO::FETCH_ASSOC)['total_seguindo'];
		}

		public function getTotalSeguidores() {
			$query = 'SELECT COUNT(*) AS total_seguidores FROM usuarios_seguidores WHERE id_usuario_seguindo = :id_usuario';

			$pdoStatement = $this->db->prepare($query);

			$pdoStatement->bindValue(':id_usuario', $this->__get('id'));
			$pdoStatement->execute();

			return $pdoStatement->fetch(\PDO::FETCH_ASSOC)['total_seguidores'];
		}
	}
?>