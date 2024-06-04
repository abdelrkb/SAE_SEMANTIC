
# 🙈 Wanky Monkey's Words 🙉

*Jeu réalisé par Maloum Elyas, Sarboudine Thamiz, Rekkab Abdelnour, Amouche Chamsedine*

## Le Jeu

Ce projet est un jeu combinant réflexion et justesse. Le but ? Faire le meilleur score possible. Comment ? Rien de plus simple :
1. Deux mots de départ vous sont donnés, par exemple "Banane" et "Singe".
2. Vous devez trouver des mots qui seront proches de ces deux derniers.
3. La proximité des mots est déterminée par :
   * La similarité lexicographique
   * La similarité sémantique
4. La similarité entre deux mots correspond à la moyenne des deux scores (lexicographique et sémantique).
5. Vous suivrez votre partie à travers un arbre listant tous les mots que vous aurez entrés avec le score de similarité entre chacun d'eux.
6. L'arbre évolue au cours de la partie, et le score final correspondra à la similarité la plus faible sur votre arbre. Il faut donc vite améliorer ce score pour obtenir le meilleur résultat.
7. La version multijoueur se déroule de la même manière en collaboration avec vos amis !

Sur le site web, vous pourrez suivre les scores de vos parties, consulter le classement des meilleurs joueurs et accéder aux règles en cas d'oubli.

## Déployer le Projet

Pour déployer le projet, voici les étapes à suivre :

### Java

Le jeu implémente du code en Java. Pour l'exécuter :
1. Télécharger, extraire et placer la JDK 21.0.3 de Java dans le répertoire un niveau au-dessus du répertoire du jeu.
   * Par exemple : Dans le répertoire "WWW-Perso", placez la JDK, puis dans "WWW-Perso/SAE", placez le projet.

### C

Le jeu implémente aussi du C. Pour compiler le code lors de sa première utilisation :

1. Effectuez les commandes gcc dans le répertoire game/C/ pour compiler le code :

   ```sh
   gcc src/dictionary_lookup.c src/lex_offset.c src/cstree.c src/export.c -o ./bin/dictionary_lookup
   gcc src/lev_similarity.c src/lev_fonctions.c -o ./bin/lev_similarity
   gcc src/word2vec_dict_builder.c src/cstree.c src/export.c -o ./bin/build_lex_index
   gcc src/sem_similarity.c src/sem_fonctions.c src/cstree.c src/export.c src/lex_offset.c -o  ./bin/sem_similarity -lm
   gcc src/newGame.c src/cstree.c src/export.c src/sem_fonctions.c src/lev_fonctions.c src/lex_offset.c -o ./bin/new_game -lm
   gcc src/addWord.c src/cstree.c src/export.c src/sem_fonctions.c src/lev_fonctions.c src/lex_offset.c -o ./bin/add_word -lm
   ```

2. Pour générer le fichier .lex, appelez build_lex_index avec le dictionnaire .bin en argument pour obtenir "arbre_lexicographique.lex" :

   ```sh
   ./bin/build_lex_index frWac_non_lem_no_postag_no_phrase_200_cbow_cut100.bin
   ```

### Multijoueur

Bien que le mode multijoueur ne fonctionne pas encore en ligne, il sera disponible en local. Pour le faire fonctionner :
1. Suivez la méthodologie du README du projet [Chatac](https://gitlab.com/codefish42/chatac).
2. Lancez le multijoueur en local après nous avoir demandé le mot de passe (il sera donné aux enseignants pour le rendu).

Pour le moment, nous n'avons réussi qu'à implémenter un chat en raison de problèmes techniques. Le jeu sera cependant disponible très bientôt !

## Bugs et Fonctionnalités Non Encore Implémentées

Le mode multijoueur n'a pas encore été implémenté en ligne en raison de l'impossibilité d'utiliser des websockets sur les serveurs de l'université. Il est en cours d'implémentation en local.

## Fusion du Groupe

Notre groupe pour ce projet a été créé à partir de deux groupes séparés qui avaient débuté le développement du jeu chacun de leur côté. Voici les choix que nous avons faits :

* Le jeu du groupe de Thamiz et Chamsedine était le plus avancé, nous avons donc décidé de partir sur cette base.
* Au niveau du C, Chamsedine, responsable de cette partie, avait effectué un excellent travail. Nous avons seulement modifié la fonction `findWord` pour améliorer les performances. Les appels à cette fonction ont été corrigés pour utiliser le fichier `.lex` plutôt qu'un `staticTree`.
* Les fichiers `newGame.c` et `addWord.c` ont été modifiés pour passer le dictionnaire (.bin), l'arbre lexicographique et le répertoire contenant les fichiers de parties en paramètres, permettant l'utilisation en solo ou en multijoueur.
* Au niveau du Java, Thamiz et Elyas ont fusionné leurs codes, corrigé les bugs liés aux scores grâce à l'algorithme Prim, au calcul du MST et à la prise en compte du dernier arbre, ce qui a permis un jeu solo complètement fonctionnel.
* Le site web a été récupéré du groupe de Thamiz et Chamsedine, avec des améliorations de style et des idées de base de données ajoutées par Abdelnour et Elyas. L'identité du groupe ayant réalisé Semonkey a été récupérée.
