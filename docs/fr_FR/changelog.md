# Changelog plugin DigiAction

>**IMPORTANT**
>
>Pour rappel s'il n'y a pas d'information sur la mise à jour, c'est que celle-ci concerne uniquement de la mise à jour de documentation, de traduction ou de texte.

# 09/03/2021  
Fixes:  
- autoriser les autres utilisateurs que 'admin'
- rafraichissement du widget si mode activé par un scénario


# 25/02/2021

Nouveautés :
- si un timer est défini :
  - le code utilisateur saisie est d'abord vérifié (inutile d'attendre la fin du timer pour savoir que le code n'est pas bon !)
  - les pré-check sont réalisés une première fois avant de lancer le timer
- ajout de la configuration "pré-check erreur" : ensemble des actions à réaliser si les contrôles échouent
- En cas d’erreur de code : on reste sur le clavier plutôt que de revenir au menu

Fixes : 
- Retouches CSS :
  - Adaptation de la taille du texte au bouton (surtout en cas de texte long)
  - Alignement des boutons (mode) « texte » et des boutons « images »
  - Alignement du compte à rebours et bouton « Annuler »
- En cas d’erreur de code successif, le message d'erreur « Code inconnu » est réaffiché  
- Gère le renommage des commandes plutôt que les suppressions  


# 19/02/2021

- Création du plugin
