<?php
	namespace App\Models;
	use MF\Model\Model;

	class Tweet extends Model {
		private $id;
		private $id_usuario;
		private $tweet;
		private $data;

		public function __get($atributo) {
			return $this->$atributo;
		}

		public function __set($atributo, $valor) {
			$this->$atributo = $valor;
		}

		public function salvar() {
			$query = 'INSERT INTO tweets(id_usuario, tweet) VALUES(:id_usuario, :tweet)';
			$pdoStatement = $this->db->prepare($query);

			$pdoStatement->bindValue(':id_usuario', $this->__get('id_usuario'));
			$pdoStatement->bindValue(':tweet', $this->__get('tweet'));

			$pdoStatement->execute();
		}

		public function getAll() {
			$query = "
				SELECT
					tweets.id, tweets.id_usuario, usuarios.nome, tweets.tweet, DATE_FORMAT(tweets.data, '%d/%m/%Y %H:%i') as data
				FROM
					tweets LEFT JOIN usuarios ON(tweets.id_usuario = usuarios.id)
				WHERE
					id_usuario = :id_usuario OR tweets.id_usuario IN(
						SELECT id_usuario_seguindo FROM usuarios_seguidores WHERE id_usuario = :id_usuario
					)
				ORDER BY 
					data DESC
			";
			
			$pdoStatement = $this->db->prepare($query);
			$pdoStatement->bindValue(':id_usuario', $this->__get('id_usuario'));

			$pdoStatement->execute();

			return $pdoStatement->fetchAll(\PDO::FETCH_ASSOC);
		}
	}
?>