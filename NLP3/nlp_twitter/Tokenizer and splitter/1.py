import tokenizer
import re


def Splitter(twits):
    ends_list = ['EMOJI', 'HASHTAG', 'GEO', 'SMILE', 'URL']
    end_tag = '-\t<END>\t\r'
    f = open('output_s.txt', 'w', encoding='utf-8')
    for twit in twits:
        splitted = []
        info = twit[1]
        tags = [x[0] for x in info]
        n_tags = iter(range(len(tags)))
        len_tags = len(tags) - 1
        if 'N' not in tags and 'FIN_PUNCT' not in tags:
            splitted = info
        elif ('N' in tags and 'FIN_PUNCT' not in tags):
            for i in n_tags:
                if tags[i] == 'N':
                    splitted.append(['-', end_tag])
                else:
                    splitted.append(info[i])
        elif 'FIN_PUNCT' in tags:
            for i in n_tags:
                splitted.append(info[i])
                if tags[i] == 'FIN_PUNCT':
                    if i != len_tags and tags[i + 1] not in ends_list:
                        splitted.append(['-', end_tag])
                    elif i != len_tags and tags[i + 1] in ends_list:
                        it = 1
                        while i + it < len_tags and tags[i + it] in ends_list:
                            splitted.append(info[i + it])
                            it += 1
                        if tags[-1] not in ends_list:
                            splitted.append(['-', end_tag])
                        for x in range(it - 1):
                            try:
                                next(n_tags)
                            except StopIteration:
                                break
        if splitted != []:
            if splitted[-1] != end_tag:
                splitted.append(['', end_tag])
            f.write(str(twit[0]) + '\n')
            for i in splitted:
                f.writelines(i[1])
            f.write('\r\n')
    f.close()


def take_emojis():
    f = open('emojis.txt', 'r', encoding='utf-8')
    emo = [line.strip() for line in f]
    emojis = '[' + '|'.join(emo) + ']'
    f.close()
    return emojis


def take_abbs():
    f = open('abbs.txt', 'r', encoding='utf-8')
    abb = [line.strip('\n') for line in f]
    abb = [re.escape(x) for x in abb]
    abbs = '(?:[ ^])' + '|'.join(abb) + '(?:[ \)$])'
    f.close()
    return abbs


def take_twits(twitter):
    f = open(twitter, 'r', encoding='utf-8')
    twit = normalizer(f.read().lower())
    f.close()
    twit = twit.split('\n\n')
    return twit


T = tokenizer.TweetTokenizer()

emojis = take_emojis()
abbs = take_abbs()

scanner = re.Scanner([
    (abbs, lambda scanner, token: ("ABBR", token)),
    (r"((https?:\/\/|www)|\w+\.(\w{2-3}))([\w\!#$&-;=\?\-\[\]~]|%[0-9a-fA-F]{2})+",
     lambda scanner, token: ("URL ", token)),
    (r"(?:\S*…)", lambda scanner, token: ("POW ", token)),  # part of word
    (r"[ \-\(\)\d]{6,}", lambda scanner, token: ("PHONE", token)),
    (r"(?:@\w+)", lambda scanner, token: ("USER", token)),
    (r"(?:\#\w+)", lambda scanner, token: ("HASHTAG", token)),
    (r"[\w.+-]+@[\w-]+\.(?:[\w-]\.?)+[\w-]", lambda scanner, token: ("EMAILS", token)),
    (r"(?:I'm at .*? in .*?(?: w/ @[\w])?|\(@ .*?\))", lambda scanner, token: ("GEO ", token)),
    (r"^(RT)", lambda scanner, token: ("RETWIT", token)),
    (emojis, lambda scanner, token: ("EMOJI", token)),
    (r"(?:[<>]?[:;=8][\-o\*\']?[\)\]\(\[dDpP*/\:\}\{@\|\\]|[\)\]\(\[dDpPсСрР3/\:\}\{@\|\\][\-o\*\']?[:;=8][<>]?)",
     lambda scanner, token: ("SMILE", token)),
    (r'[$|%|&|"|,|;|:|-|*|Р’В«|Р’В»|\(|\)|-]+', lambda scanner, token: ("PUNCT", token)),
    (r'(?:[\.\?!]{1,})', lambda scanner, token: ("FIN_PUNCT", token)),
    (r"\w+(?:-\w+)+", lambda scanner, token: ("DASH", token)),
    (r"(\w*\d+\w*)+", lambda scanner, token: ("LEX_NUM", token)),
    (r"[\n]", lambda scanner, token: ("N", token)),
    (r"(?:[+\-]?\d+[,/.:-]\d+[+\-]?)", lambda scanner, token: ("NUMB", token)),
    (r"(?:[\w_]+)", lambda scanner, token: ("WORD", token)),
    (r"(?:\S)", lambda scanner, token: ("REST", token))
], re.UNICODE)


def normalizer(text):
	text = re.sub(u'( )\\1{1,}', ' ', text)
	text = re.sub(u'(!)\\1{2,}', '!!!', text)
	text = re.sub(u'(\?)\\1{2,}', '???', text)
	text = re.sub(u'(\.)\\1{2,}', '...', text)
	text = re.sub(u'(\w)\\1{2,}', u'\\1\\1\\1', text)
	return text

def Tokenize(tweet):
	f2 = open('output.txt', 'w', encoding='utf-8')
	all_t = []
	for i in range(len(tweet)):
		tokens = T.tokenize(tweet[i])
		f2.write(str(i) + '\n')
		num = [i]
		strings = []
		for i in range(len(tokens)):
			results, remainder = scanner.scan(tokens[i])
			for j in results:
				if j[0] != 'N':
					x = str(i) + '\t' + j[0] + '\t' + j[1] + '\r'
					f2.write(str(i) + '\t' + j[0] + '\t' + j[1] + '\r')
				else:
					x = '-\t' + j[0] + '\t-\r'
				tok = [j[0], x]
				strings.append(tok)
		num.append(strings)
		all_t.append(num)
		f2.write('\n')

	f2.close()
	return all_t

tweet = take_twits('./path/to/text.txt') #вставьте сюда путь к документу
all_t = Tokenize(tweet)
Splitter(all_t)
