import json
import re


class Word():
    def __init__(self, **params):
        vars(self).update(params)

    def __repr__(self):
        return str(vars(self))
    
    def __eq__(self, other): 
        return self.__dict__ == other.__dict__

		
def get_wordforms(filename):  # словарь всех словоформ
	wordar = []
	file = open(filename, 'r', encoding='utf-8')
	for i in file:
		line = json.loads(i)
		if 'analysis' in line.keys():
			wordar.append(line)
	return wordar
	

def freq(*values):
	freq_list = {}
	for i in values:
		if i in freq_list:
			freq_list[i] += 1
		else:
			freq_list[i] = 1
	max_v = max(list(freq_list.values()))
	for k, v in freq_list.items():
		if v == max_v:
			return k
	
	
def add_wordform(w):
	word_dict = {}
	word_dict['wordform'] = w['text'].lower()
	word_dict['amount'] = len(w['analysis'])
	if word_dict['amount'] > 0:
		word_dict['freq_lemma'] = freq(*[i['lex'] for i in w['analysis']])
		word_dict['freq_pos'] = freq(*[re.search('^(\\w*?)[,=]',i['gr']).group(1) for i in w['analysis']])
	else:
		word_dict['freq_lemma'] = None
		word_dict['freq_pos'] = None
	word = Word(**word_dict)
	return word
		

def unique_wordforms():
	unique =[]
	words = get_wordforms('python_mystem.json')
	for w in words:
		word = add_wordform(w)
		if word not in unique:
			unique.append(word)
	return unique
		

print(len(unique_wordforms()))