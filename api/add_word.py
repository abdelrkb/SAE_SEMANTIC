import os
import json
import subprocess

# Fonction pour vérifier si un mot existe dans le lexique.
def word_exists_in_lexicon(word):
    command = ['./game/C/bin/dictionary_lookup', './game/C/arbre_lexicographique.lex', word]
    result = subprocess.run(command, capture_output=True, text=True)
    return result.stdout.strip() != "-1"  # Vérifier la sortie pour déterminer si le mot existe

# Fonction pour lire les données du jeu à partir d'un fichier texte et les convertir en un dictionnaire.
def read_game_data(file_path):
    game_data = {
        "Mots de départ": [],
        "Liste des mots": [],
        "Distances": {}
    }

    with open(file_path, 'r') as file:
        lines = file.readlines()
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
                game_data["Liste des mots"].append(word)
            elif current_section == "Distances":
                key, value = line.split(',', 1)
                game_data["Distances"][key.strip()] = value.split(':')[1].strip()

    return game_data

# Fonction pour lire les résultats Java et les convertir en JSON.
def convert_java_result_to_json(java_result_file, json_output_file):
    with open(java_result_file, 'r') as file:
        lines = file.readlines()

    game_data = {
        "Mots de départ": [],
        "Liste des mots": [],
        "Distances": {}
    }

    print("Reading Java result file...")  # Debug
    reading_edges = False
    for line in lines:
        line = line.strip()
        print(f"Processing line: {line}")  # Debug
        if line.startswith("Score:"):
            continue  # Skip the score line for now
        elif line.startswith("Mots de départ :"):
            current_section = "Mots de départ"
            continue
        elif line.startswith("Distance entre les mots :"):
            current_section = "Distances"
            reading_edges = True
            continue
        elif line.startswith("Mots bannis:"):
            current_section = "Mots bannis"
            reading_edges = False
            continue

        if current_section == "Mots de départ":
            print(f"Adding start word: {line}")
            game_data["Mots de départ"].append(line)
            game_data["Liste des mots"].append(line)
        elif current_section == "Distances" and reading_edges:
            if ',' in line and 'distance: ' in line:
                try:
                    key, value = line.split(', distance: ')
                    node1, node2 = key.split('_')
                    distance = value
                    game_data["Distances"][f"{node1}-{node2}"] = distance
                    game_data["Liste des mots"].append(node1)
                    game_data["Liste des mots"].append(node2)
                except ValueError as e:
                    print(f"Skipping malformed line: {line} due to error: {e}")

    game_data["Liste des mots"] = list(set(game_data["Liste des mots"]))  # Remove duplicates
    print(f"Final game data: {game_data}")  # Debug

    with open(json_output_file, 'w') as json_file:
        json.dump(game_data, json_file, ensure_ascii=False, indent=4)

# Fonction pour ajouter un mot au jeu.
def add_word_to_game(new_word, pseudo):
    # Lire les données actuelles du fichier JSON
    json_output_file = 'game_data_multi.json'
    with open(json_output_file, 'r') as json_file:
        game_data = json.load(json_file)

    # Vérifier que le nouveau mot existe dans le lexique
    if not word_exists_in_lexicon(new_word):
        raise ValueError(f"The word '{new_word}' does not exist in the lexicon.")

    # Vérifier que le mot n'est pas déjà dans la liste
    if new_word in game_data["Liste des mots"]:
        raise ValueError(f"The word '{new_word}' is already in the list of words.")

    # Exécuter le programme C pour ajouter le nouveau mot
    c_command = f'./game/C/bin/add_word ./game/C/frWac_non_lem_no_postag_no_phrase_200_cbow_cut100.bin ./game/C/arbre_lexicographique.lex ./game/partie {new_word} {pseudo}'
    print(f"Executing command: {c_command}")
    os.system(c_command)
    print("C program execution completed.")

    # Exécuter la commande Java pour traiter les résultats
    result_java_file = f'./game/partie/resultjava_{pseudo}.txt'
    game_data_file = f'./game/partie/game_data_{pseudo}.txt'
    java_command = f'../../jdk-21.0.3/bin/java -cp game/java/target/classes sae.Main {result_java_file} {game_data_file} 2>&1'
    print(f"Executing Java command: {java_command}")
    os.system(java_command)
    print("Java program execution completed.")

    # Lire le fichier de résultat Java et convertir en JSON
    convert_java_result_to_json(result_java_file, json_output_file)
    print(f"Game data JSON file updated at '{json_output_file}'")

# Fonction principale
if __name__ == "__main__":
    print("Starting add_word process...")
    new_word = input("Enter the new word to add: ").strip()
    pseudo = "multi"  # Remplacez par le pseudo réel si différent
    try:
        add_word_to_game(new_word, pseudo)
        print("Add_word process completed.")
    except ValueError as e:
        print(f"Error: {e}")