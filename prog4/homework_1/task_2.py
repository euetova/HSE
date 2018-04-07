# создаем надцарство Эукариоты

class Eukaryote:
    def __init__(self, name):
        self._name = name
        self._domain = 'Eukaryote'

    def get_info(self):
        return '%s is in %s domain' % (self._name, self._domain)
		
# создаем царства

class Animal(Eukaryote):
    def __init__(self, name):
        super().__init__(name)
        self._kingdom = 'Animal'
        
    def get_info(self):    # полиморфизм. печатаем еще и царство
        return '%s and in %s kingdom' % (super().get_info(), self._kingdom)


class Plant(Eukaryote):
    def __init__(self, name):
        super().__init__(name)
        self._kingdom = 'Plant'
        
    def get_info(self):   # полиморфизм. печатаем еще и царство
        return '%s and in %s kingdom' % (super().get_info(), self._kingdom)
		

class Cat(Animal):
    def __init__(self, name):
        super().__init__(name)
        self._species = 'Cat'
        self._voice = 'meow'
    
    def get_voice(self):
        return self._voice
		
class Dog(Animal)
    def __init__(self, name):
        super().__init__(name)
        self._species = 'Dog'
        self._voice = 'woof'
    
    def get_voice(self):  # полиморфизм - разные животные издают раздые звуки
        return self._voice

		
class Chamomile(Plant):
    def __init__(self, name):
        super().__init__(name)
        self._species = 'Chamomile' # полиморфизм. разные значения вида