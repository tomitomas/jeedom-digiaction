# Plugin DigiAction


Ce plugin permet de gérer plusieurs équipements DigiAction :  
appliquer un mode réalisant une/des action(s) sous condition(s), et d'ajouter un contrôle par saisie de mot de passe (facultatif) via un digicode.  

<img src="..\img\digiAction_présentation_dashboard.png" width="50%" />


# Configuration des DigiActions

La configuration des équipements DigiAction est accessible à partir du menu `Plugins`, sous la catégorie `Sécurité`  
  
<img src="..\img\digiAction_configuration.png" width="35%" />    
<br/><br/>

L'ajout d'un nouvel équipement se fait comme sur n'importe quel autre plugin, simplement en cliquant sur `Ajouter`    
<br/>
<img src="..\img\digiAction_all_equipements.png" width="25%" />  

<br/>
Trois onglets de configuration sont disponibles :    

1. Equipement
2. Actions 
3. Utilisateurs  
<br/><br/>

# Equipement  

Vous retrouvez ici toute la configuration standard de votre équipement :  

<img src="..\img\digiAction_equipement.png" width="50%" />  
<br/><br/>

* Nom de l’équipement : définit nom de votre équipement DigiAction,
* Objet parent : indique l’objet parent auquel appartient l’équipement,
* Catégorie : catégorise l’équipement (il peut appartenir à plusieurs catégories)
* Activer : permet de rendre votre équipement actif,
* Visible : rend votre équipement visible sur le dashboard
<br/><br/>  
  
# Actions  

Cet onglet contient les principales configurations du widgets.  
C'est ici que vous définissez : 

* les modes disponibles et leurs caractériques
* les contrôles à effectuer (en jaune)  
* les actions à réaliser (en vert)  

<img src="..\img\digiAction_onglet_actions.png"  /> 

1. le bouton `Ajouter mode` permet d'ajouter un nouveau bloc définissant un mode
  <br/>(dans l'exemple : 1a, 1b, 1c => 3 blocs définis pour cet équipement)
2. le nom du mode (cliquer dessus permet de le renommer)  
3. personnalise le mode avec un icône  (si aucun icône n'est sélectionné, alors le nom du mode sera affiché sur le widget)
4. défini un délai (en secondes) avant que le mode s'active (permet d'annuler l'action pendant un laps de temps)  
5. demande un mot de passe afin de passer sur ce mode  
6. ajoute un/des contrôle(s) à réaliser avant (l'ensemble des `Pré-check` utilisent l'opérateur 'ET')   
  6a. active/désactive le contrôle  
  6b. ouvre un pop-up pour sélectionner une commande de type `info`  
7. ajoute une/des action(s) à réaliser si jamais le(s) pré-check (défins en 6) échoue(nt)   
  7a. active/désactive l'action  
  7b. permet de choisir une commande
  7c. ouvre un pop-up pour sélectionner une commande de type `action`   
  7d. défini les options de la commande   
8. ajoute une/des action(s) à réaliser pour passer dans le mode 
  8a. active/désactive l'action  
  8b. permet de choisir une commande
  8c. ouvre un pop-up pour sélectionner une commande de type `action`   
  8d. défini les options de la commande   
9. défini le(s) mode(s) accessible(s) depuis le mode en cours 
  <br/>(pour une alarme par exemple : depuis le mode 'Désactiver' on peut activer les modes 'Partiel' ou 'Total' ; par contre depuis le mode 'Total' on ne peut aller que vers 'Désactiver')

<br/>

> #### Note
> au 1er lancement, tous les modes sont affichés afin d'initialiser le widget.  


<br/><br/>

# Utilisateurs  

Permet de gérer des utilisateurs avec leur propre code personnel, à utiliser sur les modes pour lesquels un code est requis.

<img src="..\img\digiAction_onglet_users.png" width="60%" />  

Pour ajouter un code, il faut simplement :
* cliquer sur le bouton `Ajouter un code utilisateur`
* une nouvelle ligne est ajoutée : indiquer le nom de l'utilisateur et son code (chiffre de 0 à 9 ainsi que les lette `A` et `B`)

Pour enlever un code, cliquez simplement sur le sigle `-` au bout de la ligne à supprimer