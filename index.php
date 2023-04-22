<?php
header ("Cache-Control: no-cache, must-revalidate");
header ("pragma: no-cache");

try
{			
	$pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
	$bdd = new PDO('mysql:host=127.0.0.1;dbname=db_ecommerce;port=3307', 'appliStock', 'motDePasse');
	$bdd->query("SET NAMES utf8");                  

	if ($_SERVER['REQUEST_METHOD'] == 'GET')
	{
		switch($_GET['data']){

			case 'utilisateur':
				$req = $bdd->prepare('SELECT iduser, nom, prenom, mail, mdp FROM utilisateurs where iduser = :id');
				$req->execute(array('id' => $_GET['idUser']));
				$results=$req->fetch(PDO::FETCH_ASSOC);
				print(json_encode($results));
				break;

			case 'produits':

				//Requete SQL
				$sql = "SELECT id, libelle, resume, description, codecateg, path_photo, qte_stock, prix_vente_uht, dateajout, alerteStock FROM produit";


				//Filtres ACommander
				if($_GET['aCommander'] == 1){
					//Par ordre décroissant
					$sql .= " WHERE alerteStock >= qte_stock";
				}


				//Filtres Croissant
				if($_GET['asc'] == 0){
					//Par ordre décroissant
					$sql .= " ORDER BY qte_stock DESC";
				}else{
					//Par ordre croissant
					$sql .= " ORDER BY qte_stock ASC";
				}


				$debut = (int) $_GET['debut'];
				$fin = (int) $_GET['fin'];
				$req = $bdd->prepare($sql.' LIMIT :debut , :fin');
				$req->bindParam(":debut",$debut , PDO::PARAM_INT);
				$req->bindParam(":fin",$fin , PDO::PARAM_INT);
				$req->execute();
				
				$results=$req->fetchAll(PDO::FETCH_ASSOC);
			
				print(json_encode($results));
				break;
			


			case 'produit':

				//Requete SQL
				$sql = "SELECT id, libelle, resume, description, codecateg, path_photo, qte_stock, prix_vente_uht, dateajout, alerteStock FROM produit WHERE id = :idProd";
				$req = $bdd->prepare($sql);
				$req->bindParam(":idProd",  $_GET['idProduit'] , PDO::PARAM_INT);
				$req->execute();
				
				$results=$req->fetch(PDO::FETCH_ASSOC);
			
				print(json_encode($results));
				break;

			
			case 'categories':

				$req = $bdd->prepare('SELECT codecateg, libelle FROM categories');
				$req->execute();
				
				$results=$req->fetchAll(PDO::FETCH_ASSOC);
			
				print(json_encode($results));
				break;

			case 'statuts':

				$req = $bdd->prepare('SELECT id, libelle FROM statut_commande');
				$req->execute();
				
				$results=$req->fetchAll(PDO::FETCH_ASSOC);
			
				print(json_encode($results));
				break;

			case 'statutsCommande':

				//Requete SQL
				$sql = "SELECT idStatut, date FROM statuts_commandes WHERE idCommande = :idCommande";
				$req = $bdd->prepare($sql);
				$req->bindParam(":idCommande",  $_GET['idCommande'] , PDO::PARAM_INT);
				$req->execute();
				
				$lesStatuts = array();

				while($results=$req->fetch()){

					array_push($lesStatuts, [ $results['idStatut'] => $results['date']]);
				};
				
				
				print(json_encode($lesStatuts));
				break;
			
			case 'pays':

				$req = $bdd->prepare('SELECT id, libelle, abreviation, frais FROM pays');
				$req->execute();
				
				$results=$req->fetchAll(PDO::FETCH_ASSOC);
			
				print(json_encode($results));
				break;

			case 'detailsCommande':

				//Requete SQL
				$sql = "SELECT idProduit, qte FROM details_commande WHERE idCommande = :idCommande";
				$req = $bdd->prepare($sql);
				$req->bindParam(":idCommande",  $_GET['idCommande'] , PDO::PARAM_INT);
				$req->execute();
				

				$lesProduits = array();

				while($results=$req->fetch()){

					array_push($lesProduits, [ $results['idProduit'] => $results['qte']]);
				};
			
				print(json_encode($lesProduits));
				break;

			case 'commande':

				//Requete SQL
				$sql = "SELECT id,adresse,ville,cp,idPays,nom,prenom,idUser FROM commandes WHERE id = :idCommande";
				$req = $bdd->prepare($sql);
				$req->bindParam(":idCommande",  $_GET['idCommande'] , PDO::PARAM_INT);
				$req->execute();
				
				$results=$req->fetch(PDO::FETCH_ASSOC);
			
				print(json_encode($results));
				break;

			case 'commandeExiste':

				//Requete SQL
				$sql = "SELECT id FROM commandes WHERE id = :idCommande";
				$req = $bdd->prepare($sql);
				$req->bindParam(":idCommande",  $_GET['idCommande'] , PDO::PARAM_INT);
				$req->execute();
				
				$results=$req->rowCount();
			
				print(json_encode($results));
				break;
		}
	}
	else if ($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		switch($_GET['action'])
		{
			case 'authentification':
				//Si l'utilisateur est admin et que le mail correspond
				$req = $bdd->prepare('SELECT iduser, nom, prenom, mail, mdp FROM utilisateurs where mail = :mail and admin = 1');
				$req->execute(array('mail' => $_POST['mail']));
				$resultat = $req->fetch(PDO::FETCH_ASSOC);
				
				//Tester le mot de passe
				if(password_verify($_POST['mdp'], $resultat['mdp'])){
					print(json_encode($resultat));
				}
				
				break;

			case 'seuil':
				//Update le champ alerteStock du produit en parametre
				$req = $bdd->prepare('UPDATE produit SET alerteStock = :seuil where id = :idProduit');
				$req->bindParam(":idProduit",  $_POST['idProduit'] , PDO::PARAM_INT);
				$req->bindParam(":seuil",  $_POST['seuil'] , PDO::PARAM_INT);
				$req->execute();
				
				break;

			case 'statut':

				date_default_timezone_set('Europe/Paris');
        		$dateDuJour = date('Y-m-d H:i:s', time()); //Date de l'ajout du statut

				//Insert le statut 
				$req = $bdd->prepare('INSERT INTO statuts_commandes (idCommande,idStatut,date) VALUES (:idCommande,:idStatut,;date)');
				$req->bindParam(":idCommande",  $_POST['idCommande'] , PDO::PARAM_INT);
				$req->bindParam(":idStatut",  $_POST['idStatut'] , PDO::PARAM_INT);
				$req->bindParam(":date",  $dateDuJour);
				$req->execute();
				
				break;
		}
	}
	
}
catch (Exception $e)
{
	die('Erreur : ' . $e->getMessage());
}
