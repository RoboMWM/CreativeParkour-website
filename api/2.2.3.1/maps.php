<?php
require_once 'haut-api.php';

// Partage d'une map sur le site

// Données POST envoyées : ipJoueur, uuidJoueur, nomJoueur, uuidMap, nomMap, difficulte, createur, contributeurs, taille, contenu

try {
	// Recherche du serveur
	$idServ = getIdServValide ( $_POST ["uuidServ"] );
	if (! $idServ) {
		ajouterReponse ( "servInconnu", "true" );
		repondre ();
	}
	
	$set = "SET uuid = :uuidMap, cleID = :cleID, nom = :nom, dateAjout = NOW(), etat = 'creation', idServOrigine = :idServOrigine, createur = :createur, contributeurs = :contributeurs, taille = :taille, qualite = -1, difficulte = :difficulte";
	$requete = "INSERT INTO maps " . $set . ", fichierContenu = :fichierContenu";
	
	// Recherche de la map pour savoir si elle existe déjà
	$reponse = $bdd->prepare ( "SELECT id, etat FROM maps WHERE uuid = :uuidMap" );
	$reponse->execute ( array (
			"uuidMap" => $_POST ["uuidMap"] 
	) ) or erreurAPI ( "SQL error 23" );
	$donnees = $reponse->fetch ();
	if ($donnees) {
		// Si la map existe déjà
		if ($donnees ["etat"] === "creation") {
			$requete = "UPDATE maps " . $set . " WHERE uuid = :uuidMap";
		} else {
			ajouterReponse ( "mapDejaExistante", "true" );
			ajouterReponse ( "idMap", $donnees ["id"] );
			repondre ();
		}
		$reponse->closeCursor ();
	} else {
		// Enregistrement du contenu de la map dans un fichier
		$fichierMap = filtrerNomFichier ( uniqid ( $_POST ["nomMap"] . "-" ) );
		$fp = gzopen ( "../../maps data/" . $fichierMap . ".gz", "w" );
		gzputs ( $fp, $_POST ["contenu"] );
		gzclose ( $fp );
	}
	
	// Création et récupération de la clé d'accès
	$cle = nouvelleCle ( $_POST ["ipJoueur"], $_POST ["uuidJoueur"], $_POST ["nomJoueur"] );
	
	// Tronquage du nom à 60 caractères
	$nomMap = substr ( $_POST ["nomMap"], 0, 60 );
	
	// Enregistrement de la map dans la table
	$req = $bdd->prepare ( $requete );
	$req->execute ( array (
			"uuidMap" => $_POST ["uuidMap"],
			"cleID" => $cle->id,
			"nom" => $nomMap,
			"idServOrigine" => $idServ,
			"createur" => $_POST ["createur"],
			"contributeurs" => $_POST ["contributeurs"],
			"taille" => $_POST ["taille"],
			"fichierContenu" => $fichierMap,
			"difficulte" => $_POST ["difficulte"] 
	) ) or erreurAPI ( "SQL error 61" );
	$req->closeCursor ();
	
	// Enregistrement des noms
	$a = separerUuidNom ( $_POST ["createur"] );
	enregistrerNomMC ( $a ["uuid"], $a ["nom"] );
	if ($_POST ["contributeurs"]) {
		foreach ( explode ( ";", $_POST ["contributeurs"] ) as $c ) {
			$a = separerUuidNom ( $c );
			if ($a ["uuid"] && $a ["nom"])
				enregistrerNomMC ( $a ["uuid"], $a ["nom"] );
		}
	}
	
	ajouterReponse ( "cle", $cle->cle );
} catch ( Exception $e ) {
	erreurAPI ( "unknown error" );
}

repondre ();
?>