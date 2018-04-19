<?php 

 
/* Création d'une fonction check_magicquotes utilisée dans la récupération des variables - qui teste la configuration get_magic_quotes_gpc du serveur.
Si oui, supprime avec la fonction stripslashes les antislashes "\" insérés dans les chaines de caractère des variables gpc (GET, POST, COOKIE) */
	function check_magicquotes($chaine)
	{
		if(get_magic_quotes_gpc()) $chaine = stripslashes($chaine);
		return $chaine;
	}

//Initialisation du message de réponse:
	$msg = null;

//Si le formulaire est envoyé:
	if(isset($_POST['email']))
	{
		/*Récupération des variables issues du formulaire
		Teste l'existence les données post en vérifiant qu'elles existent, qu'elles sont non vides et non composées uniquement d'espaces.
		(Ce dernier point est facultatif et l'on pourrait se passer d'utiliser la fonction trim())
		En cas de succès, on applique la fonction check_magicquotes pour (éventuellement) nettoyer la variable */
		$email = (isset($_POST['email']) && trim($_POST['email']) != '')? check_magicquotes($_POST['email']) : null;
		$pw = (isset($_POST['password']) && trim($_POST['password']) != '')? check_magicquotes($_POST['password']) : null;

		//Si $email et $pw est différents de null:
		if(isset($email, $pw))
		{
			//Connexion au serveur en local:
			$hostname = "localhost";
			$database = "happy_days";
			$username = "root";
			$password = "";
			$connection = mysql_connect($hostname, $username, $password) or die(mysql_error());

			//Connexion à la BDD:
			mysql_select_db($database, $connection);

			//Indique à mySql de travailler en UTF-8 (par défaut myDSl de travailler en ISO-8859-1):
			mysql_query("SET NAMES 'utf8'");
			
			//Préparation des données pour les requêtes à l'aide de la fonction mysql_real_escape_string:
			$nom = mysql_real_escape_string($email);
			$password = mysql_real_escape_string($pw);

			//Requête pour compter le nombre d'enregistrements répondant à la clause : champ du pseudo de la table = pseudo posté dans le formulaire :
			$requete = "SELECT count(*) as nb FROM membre WHERE email = '". $nom . "'";

			//Exécution de la requête:
			$req_exec = mysql_query($requete) or die(mysql_error());

			//Création du tableau associatif du résultat:
			$resultat = mysql_fetch_assoc($req_exec);

			// nb est le nom de l'alias associé à count(*) et retourne le résultat de la requête dans le tableau $resultat:
			if(isset($resultat['nb']) && $resultat['nb'] == 0 )
			{
				//Résulat du comptage = 0 pour l'email, on peut donc l'enregistrer: 
				$insertion = "INSERT INTO membre(email,password) VALUES ('". $nom . "', '" . $password . "', NOW())";

				//Exécution de la requête d'insertion à la bdd:
				$inser_exec = mysql_query($insertion) or die(mysql_error());

				//Si l'insertion s'est faite correctement (une requête d'insertion retourne "true" en cas de succès, je peux donc utiliser l'opérateur de comparaison strict '===' ):
				if($inser_exec === true)
				{
					//Démarre la session et enregistre l'email dans la variable de session $_SESSION['login'] qui donne au visiteur la possibilité de se connecter:
					session_start();
					$_SESSION['login'] = $email;

					//Message de comfirmation d'enregistrement:
					$msg = 'Votre inscription est enregistrée. <a href = "?p=home">Cliquez ici pour vous connecter!</a>';
				}
			}
			else
			{ // L'email est déjà utilisé:
				$msg = 'Cet email est déjà utilisé, changez-le.';
			}
		}
		else
		{ //Au moins un des deux champs "pseudo" ou "mot de passe" n'a pas été rempli:
			$msg = 'Les champs "Email" et "Mot de passe" doivent être remplis.';
		}
    }

?>