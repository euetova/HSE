from itertools import permutations
from collections import Counter

# task 1. Напишите функцию anagram(word), которая возвращает все анаграммы, которые можно составить из слова word.

def anagram(word):
	return list(map("".join, permutations(word)))
	
print(anagram('word'))

# task 2. Дано уравнение: rqtr + wrt = rwuu. Разные буквы стоят на месте разных цифр. Сколько решений у этого уравнения?

perm = list(permutations('0123456789', 5))
let = ['r', 'q', 't', 'w', 'u']
k = 0

for p in perm:
	dct = dict(zip(let, p))
	digit1 = int(dct['r'] + dct['q'] + dct['t'] + dct['r'])
	digit2 = int(dct['w'] + dct['r'] + dct['t'])
	digit3 = int(dct['r'] + dct['w'] + dct['u'] + dct['u'])
	if digit1+digit2 == digit3:
		k += 1

print(k)	
	
# task 3. количество вхождений кадого символа в тексте с помощью Counter. 
# Создать множество из 20 самых частотных символов, используя set comprehension и Counter.most_common.

f = open('bubbles.txt', 'r', encoding = 'utf-8')
text = f.read()
f.close()

letters = Counter(text)
most_com = {x[0] for x in letters.most_common(20)}

print(most_com)
