import re
import os
 
def count_vowels(word):
    vowels = ('а', 'у', 'о', 'ы', 'и', 'э', 'я', 'ю', 'ё', 'е')
    return sum(letter in vowels for letter in word)
 
def search_short(text):           
	x = re.findall(r'([^# \r\n][а-яёА-ЯЁ]*?[бвгджзйклмнпрстфхцчшщ]\.) [а-яё]', text)
	x = set(x)
    #print(x)
	y = []
	for word in x:
		if count_vowels(word) <= 1:
			y.append(word.lower())
	y = set(y)
	with open('shor_our.txt', 'w', encoding='utf-8') as outfile:
		for line in sorted(list(y)):
			outfile.write(line + '\r\n')
			
def all_twits():
	alltwits = ''
	for root, dirs, files in os.walk('.'):
		for fname in files:
			if fname != 'abbr.txt':
				point = fname.rfind('.')
				extension = fname[point + 1:]
				if extension == 'txt':
					file = open(root+'/'+fname, 'r+', encoding = 'utf-8')
					alltwits += file.read()
					file.close()
	return alltwits
 
 
alltwits = all_twits()      

search_short(alltwits)