from collections import Counter
import re
import string
import pandas as pd
import time

class Profiler(object):
    def __enter__(self):
        self._startTime = time.time()

    def __exit__(self, type, value, traceback):
        print ("Elapsed time: {:.3f} sec".format(time.time() - self._startTime))

def spacing(text):
    spaces = re.findall('[^\\w]((?:\\w ){3,}\\w)[ '+string.punctuation+'—–…“”«»'+']',text)
    for elem in spaces:
        text = text.replace(elem,elem.replace(' ',''))
    return text


def word_counter(text, mystopwords):
    '''
    Считаем слова
    :param text:
    :return:
    '''
    text = text.split()
    text_wo_sw = [i for i in text if i.lower() not in mystopwords]
    return{'len' : len(text_wo_sw)}


def punct_counter(text):
    punct = re.findall('[!?]', text)
    c = Counter(punct)
    c_punct = {'excl': c['!'], 'quest': c['?']}
    return c_punct

def emoji_counter(text):
    '''
    Считает емодзи
    :param emoji:
    :return:
    '''
    with open ('em_neg.txt', 'r', encoding='utf-8') as em_neg:
        neg_emoji = [line.strip() for line in em_neg]
        ne = '[' + '|'.join(neg_emoji) + ']'
    emoji_n = re.findall(ne, text)
    with open('em_neg.txt', 'r', encoding='utf-8') as em_pos:
        pos_emoji = [line.strip() for line in em_pos]
        pe = '[' + '|'.join(pos_emoji) + ']'
    emoji_p = re.findall(pe, text)
    c_em = {'pos_em': sum(Counter(emoji_p).values()), 'neg_em': sum(Counter(emoji_n).values())}
    return c_em

def smile_counter(text):
    '''
    Считает смайлики
    :param smiles:
    :return:
    '''
    sm_n = re.findall(r'(:\(|;\(|=\(|:-\()', text)
    sm_p = re.findall(r'(:\)|:D|: @|;\)|:-\)|=\)|:\*|:3)', text)
    c_smiles = {'pos_sm' : sum(Counter(sm_p).values()), 'neg_sm' : sum(Counter(sm_n).values())}
    return c_smiles

def ahah_counter(text):
    '''
    Считает смешки
    '''
    ahah = re.findall(r'', text)
    c_ahah = {'haha' : sum(Counter(ahah).values())}
    return c_ahah


def normalize(l):
    l = re.sub(r"((https?:\/\/|www)|\w+\.(\w{2,3}))([\w\!#$&-;=\?\-\[\]~]|%[0-9a-fA-F]{2})+", '', l) #убираем ссылки
    l = re.sub(r"(?:@\w+)", '', l)                                        # убираем пользователя
    l = re.sub(r"[\w.+-]+@[\w-]+\.(?:[\w-]\.?)+[\w-]", '', l)             # убираем email
    l = re.sub(r"(?:I'm at .*? in .*?(?: w/ @[\w])?|\(@ .*?\))", '', l)   # убираем геолокации
    l = l.replace('RT', '')                                              # убираем RT
    exclude = string.punctuation + '0123456789' + u'—' + u'«»'
    regex = re.compile('[%s]' % re.escape(exclude))
    l = regex.sub(' ', l)
    lower_up = [m.start() for m in re.finditer(r"[a-zа-яё]{1}[A-ZА-ЯЁ]{1}", l)]   # добавляем пробел между lower- и uppercase HiWorld => Hi World
    for i in reversed(lower_up):
        l = l[:i+1] + ' ' + l[i+1:]
    l = re.sub(u'(\w)\\1{2,}', u'\\1\\1', l)   # буквы больше двух раз подряд
    l = spacing(l)                                # р а з р я д к а
    l = l.lower()                                                         # уменьшаем регистр у всех букв
    return l

def merge_dicts(*dict_args):
    """
    Given any number of dicts, shallow copy and merge into a new dict,
    precedence goes to key value pairs in latter dicts.
    """
    result = {}
    for dictionary in dict_args:
        result.update(dictionary)
    return result


def twit_to_data(text, t_class, twits_data, mystopwords):
    '''
    Превращает твит в строку датафайла
    '''
    punct = punct_counter(text)
    emoji = emoji_counter(text)
    smile = smile_counter(text)
    text_n = normalize(text)
    words = word_counter(text_n, mystopwords)
    text_d = {'text': text, 'text_n' : text_n}
    t_class_d = {'class': t_class}
    md = merge_dicts(text_d, punct, emoji, smile, words, t_class_d)
    twits_data.append(md)
