package sae.java;

import java.util.List;

public record LoadingData(Word startWord, Word endWord, List<Word> words, List<Edge> edges) {

    public LoadingData(Word startWord, Word endWord, List<Word> words, List<Edge> edges) {
        this.startWord = startWord;
        this.endWord = endWord;
        this.words = words;
        this.edges = edges;
    }
}
