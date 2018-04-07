import re
import string

def spacing(text):
    spaces = re.findall('[^\\w]((?:\\w ){3,}\\w)[ '+string.punctuation+'—–…“”«»'+']',text)
    for elem in spaces:
        text = text.replace(elem,elem.replace(' ',''))
    return text

def normalisation(text):
    '''
    Принимает на вход текст, возвращает его нормализованным
    :param text: текст
    :return: нормализованный текст
    '''
    text = spacing(text) # р а з р я д к а
    text = re.sub(u'( )\\1{1,}', ' ', text) # много пробелов
    text = re.sub(u'(!)\\1{2,}', '!!!', text) # восклицательные знаки
    text = re.sub(u'(\?)\\1{2,}', '???', text) # вопросительные знаки
    text = re.sub(u'(\.)\\1{2,}', '...', text) # многоточия
    text = re.sub(u'(\w)\\1{2,}', u'\\1\\1', text) # буквы больше двух раз подряд
    return text

#text = 'ааааааа  привеееетики!!!!!! как твои д е л и и и и и ш к и?!?!????!'
