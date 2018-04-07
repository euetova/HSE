# сложность O(T+n_1P_1+n_2P_2+...+n_iP_i), где 1, 2, ..., i - это индексы, n_i - количество вхождений i-того паттерна в текст,
# Т - длина текста, Р_i - длина i-того паттерна

import numpy as np
from unittest import *

def poly_hash(s, x=31, p=997):
    h = 0
    for j in range(len(s)-1, -1, -1):
        h = (h * x + ord(s[j]) + p) % p
    return h
	
def search_rabin_multi(text, patterns):
    p = 997
    x = 31
    indices = []
    
    min_len = min([len(x) for x in patterns])
    if len(text) < min_len:
        return [[] for x in range(len(patterns))]
    
    for pattern in patterns:
        pattern_indices = []

        if len(text) < len(pattern) or not pattern:
            indices.append([])
            continue   
    
        # precompute hashes
        precomputed = [0] * (len(text) - len(pattern) + 1)
        precomputed[-1] = poly_hash(text[-len(pattern):], x, p)
    
        factor = 1
        for i in range(len(pattern)):
            factor = (factor*x + p) % p
        
        for i in range(len(text) - len(pattern)-1, -1, -1):
            precomputed[i] = (precomputed[i+1] * x + ord(text[i]) - factor * ord(text[i+len(pattern)]) + p) % p
            
        pattern_hash = poly_hash(pattern, x, p)
        for i in range(len(precomputed)):
            if precomputed[i] == pattern_hash:
                if text[i: i + len(pattern)] == pattern:
                    pattern_indices.append(i)

        indices.append(pattern_indices)
    
    return indices
	
	
class SearchRabinMultiTest(TestCase):
    def test_small_text(self):
        self.assertEqual([[],[]],search_rabin_multi('t',['text','pattern']))

    def test_no_pattern(self):
        self.assertEqual([[]],search_rabin_multi('text',['']))
	
    def test_correct_output(self):
        self.assertEqual([[1]],search_rabin_multi('text',['ex']))
        self.assertEqual([[],[2]],search_rabin_multi('text',['pa','xt']))
        self.assertEqual([[0,3,4,7],[1,5]],search_rabin_multi('texttext',['t','ex']))

    def test_overlap(self):
        self.assertEqual([[1],[1]],search_rabin_multi('text',['ex','ext']))
        self.assertEqual([[1,2, 3, 4, 5]],search_rabin_multi('teeeeeext',['ee']))
		
# case = SearchRabinMultiTest()
# suite = TestLoader().loadTestsFromModule(case)
# TextTestRunner().run(suite)
