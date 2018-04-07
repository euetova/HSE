import nltk
from nltk.tokenize import WhitespaceTokenizer
import os


def tokens(file):  # находит токены
	file = ''.join(ch for ch in file)
	tkns = WhitespaceTokenizer().tokenize(file.lower()) 
	return tkns

def freq(file, word):
    t = tokens(file)
    k = 0
    for i in t:
        if i == word:
            k += 1
    return (k*1000000)/len(t)
	
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
	
def take_abbr(fname):		
	f = open(fname, 'r', encoding = 'utf-8')
	sokr = f.readlines()
	sokr = [i.strip().lower() for i in sokr]
	f.close()
	return sokr
	
def make_dct(alltwits, sokr):
	freq_dct = {}
	for s in sokr:
		freq_dct[s] = freq(alltwits, s)
	return freq_dct
	
def write_in_txt(fname, freq_dct):	
	fout = open(fname, 'w', encoding = 'utf-8')
	for w in sorted(freq_dct, key=freq_dct.get, reverse=True):
		fout.write(w+'; \t'+str(freq_dct[w])+'\n')
	fout.close()

	
sokr = take_abbr('abbr.txt')
alltwits = all_twits()
freq_dct = make_dct(all_twits, sokr)
	
write_in_txt('freq_our.txt', freq_dct)
