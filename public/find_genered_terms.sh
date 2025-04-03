#!/bin/bash

# List of patterns to search (case-insensitive)
TERMS=(
    '\bdomme\b'
    '\bsubmissive\b'
    'Domme'
    'Submissive'
    'Mistress'
    'master'
    'she/her'
    'he/him'
    'hers'
    'his'
    'female dom'
    'male sub'
)

echo "🔍 Scanning for gendered or outdated terms in codebase..."

for term in "${TERMS[@]}"; do
    echo -e "\n📌 Searching for: $term"
    grep -RiIn --color=always "$term" ./ | grep -v "\.git/"
done

echo -e "\n✅ Scan complete."

