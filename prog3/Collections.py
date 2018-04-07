from collections import Counter
from collections import defaultdict

f = open('bubbles.txt', 'r', encoding = 'utf-8')
text = f.read()
f.close()

# task 1. количество вхождений каждого символа в тексте
letters = Counter(text)
print(letters)

# task 2. Предположим каждое слово в тексте пронумеровано. 
# Написать функцию, которая для каждой словоформы в тексте распечатывает все номера, соответствующие ей. Регистр нужно игнорировать.

words = text.lower().split()

def word_position(text):
	w_dct = defaultdict(list)
	for w in range(len(text)):
		w_dct[text[w]].append(w)
	return w_dct
	
print(word_position(words))
