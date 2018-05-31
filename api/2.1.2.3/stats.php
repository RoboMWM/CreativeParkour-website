<?php
require_once 'haut-api.php';

// Reçu des envois de statistiques
// Données POST envoyées : nbJoueurs, nbMaps, nbJoueursCP, secondesJouees, parkoursTentes, parkoursReussis, nbSauts, langue, onlineMode, infosPlugin, versionServeur
try {
	
	$servID = getServIDAvecUUID ( $_POST ["uuidServ"] );
	
	// Ajout des données dans la table
	$req = $bdd->prepare ( "INSERT INTO statistiques (date, idServ, uuidServ, ipConnexion, nbJoueurs, nbMaps, nbJoueursCP, secondesJouees, parkoursTentes, parkoursReussis, nbSauts, langue, onlineMode, infosPlugin, versionServeur) VALUES (NOW(), :idServ, :uuidServ, :ipConnexion, :nbJoueurs, :nbMaps, :nbJoueursCP, :secondesJouees, :parkoursTentes, :parkoursReussis, :nbSauts, :langue, :onlineMode, :infosPlugin, :versionServeur)" );
	$req->execute ( array (
			"idServ" => $servID,
			"uuidServ" => $_POST ["uuidServ"],
			"ipConnexion" => getUserIP(),
			"nbJoueurs" => $_POST ["nbJoueurs"],
			"nbMaps" => $_POST ["nbMaps"],
			"nbJoueursCP" => $_POST ["nbJoueursCP"],
			"secondesJouees" => $_POST ["secondesJouees"],
			"parkoursTentes" => $_POST ["parkoursTentes"],
			"parkoursReussis" => $_POST ["parkoursReussis"],
			"nbSauts" => $_POST ["nbSauts"],
			"langue" => $_POST ["langue"],
			"onlineMode" => $_POST ["onlineMode"],
			"infosPlugin" => $_POST ["infosPlugin"],
			"versionServeur" => $_POST ["versionServeur"] 
	) ) or erreurAPI ( "SQL error 27" );
	$req->closeCursor ();
} catch ( Exception $e ) {
	erreurAPI ( "unknown error" );
}

repondre ();
?>