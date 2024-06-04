import os
import json
import random
import subprocess

# Fonction pour obtenir un mot aléatoire dans la liste des mots disponibles.
def get_random_word(exclude=[]):
    with open('game/Liste_mots.txt') as f:
        content = f.read()
        words = content.split(';')  # Diviser le contenu en utilisant le point-virgule comme séparateur
    word = random.choice(words).strip()
    while word in exclude or not word_exists_in_lexicon(word):
        word = random.choice(words).strip()
    return word

# Fonction pour vérifier si un mot existe dans le lexique.
def word_exists_in_lexicon(word):
    command = ['game/C/bin/dictionary_lookup', 'game/C/arbre_lexicographique.lex', word]
    result = subprocess.run(command, capture_output=True, text=True)
    print(result)
    return result.stdout.strip() != "-1"  # Vérifier la sortie pour déterminer si le mot existe

# Fonction pour lire les données du jeu à partir d'un fichier texte et les convertir en un dictionnaire.
def read_game_data(file_path):
    with open(file_path, 'r') as file:
        lines = file.readlines()

    game_data = {
        "Mots de départ": [],
        "Liste des mots": [],
        "Distances": {}
    }

    current_section = None

    for line in lines:
        line = line.strip()
        if line == "Mots de départ :":
            current_section = "Mots de départ"
            continue
        elif line == "Liste des mots :":
            current_section = "Liste des mots"
            continue
        elif line == "Distance entre les mots :":
            current_section = "Distances"
            continue

        if current_section == "Mots de départ":
            word = line.split(',')[0]
            game_data["Mots de départ"].append(word)
            game_data["Liste des mots"].append(word)  # Initialement, la liste des mots est la même que les mots de départ
        elif current_section == "Liste des mots":
            continue  # Ignorer cette section car elle est déjà traitée dans "Mots de départ"
        elif current_section == "Distances":
            key, value = line.split(',', 1)
            game_data["Distances"][key.strip()] = value.split(':')[1].strip()

    return game_data

# Fonction pour démarrer le jeu.
def start_game():
    # Générer les mots aléatoires
    print("Generating random words...")
    mot1 = get_random_word()
    mot2 = get_random_word(exclude=[mot1])
    print(f"Generated words: {mot1}, {mot2}")

    # Exécuter le programme C
    c_command = f'./game/C/bin/new_game game/C/frWac_non_lem_no_postag_no_phrase_200_cbow_cut100.bin game/C/arbre_lexicographique.lex game/partie {mot1} {mot2} multi'
    print(f"Executing command: {c_command}")
    os.system(c_command)
    print("C program execution completed.")

    # Exécuter la commande Java pour traiter les résultats
    result_java_file = f'./game/partie/resultjava_multi.txt'
    game_data_file = f'./game/partie/game_data_multi.txt'
    with open(result_java_file, 'w') as result_file:
        pass  # Just to create the file with the correct permissions

    java_command = f'../../jdk-21.0.3/bin/java -cp game/java/target/classes sae.Main {result_java_file} {game_data_file} 2>&1'
    os.system(java_command)

    # Lire le fichier de résultat généré par le programme C
    game_data_file = f"./game/partie/game_data_multi.txt"
    if not os.path.exists(game_data_file):
        raise FileNotFoundError("Game data file not found")
    print(f"Reading game data from: {game_data_file}")

    game_data = read_game_data(game_data_file)

    # Écrire les données dans un fichier JSON
    result_json_file = f"./game_data_multi.json"
    with open(result_json_file, 'w') as json_file:
        json.dump(game_data, json_file, ensure_ascii=False, indent=4)

    print(f"Game data JSON file created at {result_json_file}")

    # Retourner les données du jeu sous forme de dictionnaire
    return game_data

if __name__ == "__main__":
    print("Starting game setup...")
    start_game()
    print("Game setup completed.")