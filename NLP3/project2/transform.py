import string

punkt = string.punctuation+'»«-'


def gold_standard(line):
	if line[0] in punkt:
		rez = line[0] + '\t\t\t'
		return rez
	rez = line[0] + '\t' + line[2] + '\t'
	tag = line[1]
	gender = {'m': 'm', 'f': 'f', 'n': 'n', 'c': 'c'}
	number = {'s': 'sg', 'p': 'pl'}
	case = {'n': 'nom', 'g': 'gen', 'd': 'dat', 'a': 'acc', 'v': 'voc', 'l': 'loc', 'i': 'ins'}
	case2 = {'p': 'part', 'l': 'loc'}
	vform = {'i':'indicative', 'm':'imper', 'n':'inf', 'p':'partcp', 'g':'ger'}
	tense = {'p':'pres', 'f':'fut', 's':'past'}
	person = {'1':'1p', '2':'2p', '3':'3p'}
	voice = {'a':'act', 'p':'pass'}
	defin = {'s':'brev'}
	aspect = {'p':'progressive', 'e':'perfective', 'b':'biaspectual'}
	degree = {'c':'comp', 's':'supr'}
	new_tag = []
	if tag[0] == 'N':
		rez += 'S\t'
		if len(tag) == 7:
			if tag[6] in case2:
				new_tag.append(case2[tag[6]])
		if tag[2] in gender:
			new_tag.append(gender[tag[2]])
		if tag[4] in case:
			new_tag.append(case[tag[4]])
		if tag[3] in number:
			new_tag.append(number[tag[3]])
		rez += ','.join(new_tag)
		return rez
	
	elif tag[0] == 'V':
		rez += 'V\t'
		if tag[2] == 'n':
			rez += 'inf'
			return rez
		elif tag[2] == 'm':
			new_tag.append(vform[tag[2]])
			new_tag.append(person[tag[4]])
			new_tag.append(number[tag[5]])
		elif tag[2] == 'g':
			new_tag.append(vform[tag[2]])
			new_tag.append(tense[tag[3]])
		elif tag[2] == 'i':
			new_tag.append(tense[tag[3]])
			if tag[4] in person:
				new_tag.append(person[tag[4]])
			elif tag[6] in gender:
				new_tag.append(gender[tag[6]])
			new_tag.append(number[tag[5]])
		elif tag[2] == 'p':
			new_tag.append(vform[tag[2]])
			new_tag.append(voice[tag[7]])
			new_tag.append(tense[tag[3]])
			new_tag.append(number[tag[5]])
			if tag[6] in gender:
				new_tag.append(gender[tag[6]])
			if len(tag) == 11 and tag[10] in case:
				new_tag.append(case[tag[10]])
			else:
				new_tag.append('nom')
			if tag[8] == 's':
				new_tag.append(defin[tag[8]])
		rez += ','.join(new_tag)
		return rez
		
	elif tag[0] == 'A':
		rez += 'A\t'
		if tag[4] in number:
			new_tag.append(number[tag[4]])
		if tag[3] in gender:
			new_tag.append(gender[tag[3]])
		if tag[5] in case:
			new_tag.append(case[tag[5]])
		if tag[6] in defin:
			new_tag.append(defin[tag[6]])
		if tag[2] in degree:
			new_tag.append(degree[tag[2]])
		rez += ','.join(new_tag)
		return rez
		
	elif tag[0] == 'P':
		syn_type = {'n':'SPRO', 'a':'APRO', 'r':'ADVPRO'}
		if tag[6] in syn_type:
			rez += syn_type[tag[6]] + '\t'
		if tag[2] in person:
			new_tag.append(person[tag[2]])
		if tag[3] in gender:
			new_tag.append(gender[tag[3]])
		if tag[5] in case:
			new_tag.append(case[tag[5]])
		if tag[4] in number:
			new_tag.append(number[tag[4]])
		rez += ','.join(new_tag)
		return rez
		
	elif tag[0] == 'R':
		rez += 'ADV\t'
		#if tag[2] in degree:
		#	rez += degree[tag[2]]
		return rez
		
	elif tag[0] == 'S':
		rez += 'PR\t'
		return rez
		
	elif tag[0] == 'C':
		rez += 'CONJ\t'
		return rez
		
	elif tag[0] == 'M':
		if tag[1] == 'o':
			rez += 'ANUM\t'
			if len(tag) > 5 and tag[4] in case:
				new_tag.append(case[tag[4]])
			if len(tag) > 3 and tag[2] in gender:
				new_tag.append(gender[tag[2]])
			if len(tag) > 4 and tag[3] in number:
				new_tag.append(number[tag[3]])
			rez += ','.join(new_tag)
		else:
			rez += '\t'
		return rez
		
	elif tag[0] == 'Q':
		rez += 'PART\t'
		return rez
		
	elif tag[0] == 'I':
		rez += 'INTJ\t'
		return rez
		
	elif tag[0] == 'Y':
		rez += 'Abbr\t'
		return rez
		
	elif tag[0] == 'X':
		rez = '\t'
		return rez
	else:
		return line[0] + '\t\t\t'



f = open('treetagger.txt', 'r', encoding = 'utf-8')
output = open('output.txt', 'w', encoding = 'utf-8') 
gold = open('GoldStandard.txt', 'r', encoding = 'utf-8') 
gs = gold.readlines()
text = f.readlines()
output.write('Wordform\tLemma\tPOS\tGram\n')
correct = 0
corr_lem = 0
gs_list = []
tt_list = []

for t in range(len(text)):
	line = text[t].split()
	gs_line = gs[t+1].split()
	tt_line = gold_standard(line)
	output.write(str(tt_line) + '\n')
	tt_line = tt_line.split()
	if len(gs_line) == 4:       # в treetagger '478,6	@card@', в gs это три разных токена, поэтому красиво не получается посчитать
		gs_list.append([gs_line[1], gs_line[3]])
	elif len(gs_line) == 3:
		gs_list.append([gs_line[1], ''])
	if len(tt_line) == 4:
		tt_list.append([tt_line[1], tt_line[3]])
	elif len(tt_line) == 3:
		tt_list.append([tt_line[1], ''])
	
length = len(gs_list)	
for t in range(length):	
	if tt_list[t][0] == gs_list[t][0]:
		corr_lem += 1
	if set(tt_list[t][1].split(',')) == set(gs_list[t][1].split(',')):
		correct += 1
			
print(corr_lem/length)
print(correct/length)
		
output.close()
f.close()
gold.close()
