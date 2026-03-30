#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script pour nettoyer les styles inline futuristes et ajouter dashboard-nature.css
"""
import os
import re
import sys
from pathlib import Path

def process_file(filepath):
    """Traite un fichier PHP pour nettoyer les styles"""
    print(f"Traitement: {filepath}", file=sys.stderr)

    # Essayer plusieurs encodages
    encodings = ['utf-8', 'latin-1', 'cp1252', 'iso-8859-1']
    content = None
    used_encoding = None

    for encoding in encodings:
        try:
            with open(filepath, 'r', encoding=encoding) as f:
                content = f.read()
                used_encoding = encoding
                break
        except (UnicodeDecodeError, LookupError):
            continue

    if content is None:
        print(f"  ERROR: Impossible de lire le fichier", file=sys.stderr)
        return False

    original_content = content
    modified = False

    # Vérifier si dashboard-nature.css est déjà présent
    has_dashboard_css = 'dashboard-nature.css' in content
    has_global_nature = 'global-nature-zoo.css' in content

    # Ajouter dashboard-nature.css si global-nature-zoo.css est présent mais pas dashboard-nature
    if has_global_nature and not has_dashboard_css:
        # Extraire le chemin relatif de global-nature-zoo.css
        match = re.search(r'href="([^"]*global-nature-zoo\.css)"', content)
        if match:
            global_css_path = match.group(1)
            # Remplacer le nom du fichier
            dashboard_css_path = global_css_path.replace('global-nature-zoo.css', 'dashboard-nature.css')

            content = re.sub(
                r'(<link rel="stylesheet" href="[^"]*global-nature-zoo\.css">)',
                r'\1\n    <link rel="stylesheet" href="' + dashboard_css_path + '">',
                content,
                count=1
            )
            modified = True
            print(f"  + Ajout de dashboard-nature.css", file=sys.stderr)

    # Supprimer la balise <style> et tout son contenu (styles inline)
    style_pattern = r'<style>.*?</style>'
    if re.search(style_pattern, content, re.DOTALL):
        content = re.sub(style_pattern, '', content, flags=re.DOTALL)
        modified = True
        print(f"  - Suppression des styles inline", file=sys.stderr)

    # Sauvegarder si modifié
    if modified and content != original_content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"  OK Fichier mis a jour", file=sys.stderr)
        return True
    else:
        print(f"  SKIP Aucune modification", file=sys.stderr)
        return False

def main():
    """Fonction principale"""
    base_dir = Path(__file__).parent

    # Dossiers à traiter
    folders = [
        'Direction/animaux',
        'Direction/especes',
        'Direction/enclos',
        'Direction/user',
        'Direction/gestion_comptes',
        'Direction/reservations',
        'Employes/animaux',
        'Employes/especes',
    ]

    total_files = 0
    modified_files = 0

    for folder in folders:
        folder_path = base_dir / folder
        if not folder_path.exists():
            print(f"WARNING: Dossier non trouve: {folder}", file=sys.stderr)
            continue

        # Trouver tous les fichiers PHP
        php_files = list(folder_path.rglob('*.php'))

        for php_file in php_files:
            total_files += 1
            if process_file(php_file):
                modified_files += 1

    print(f"\n{'='*60}", file=sys.stderr)
    print(f"TERMINE!", file=sys.stderr)
    print(f"Fichiers traites: {total_files}", file=sys.stderr)
    print(f"Fichiers modifies: {modified_files}", file=sys.stderr)
    print(f"{'='*60}", file=sys.stderr)

if __name__ == '__main__':
    main()
