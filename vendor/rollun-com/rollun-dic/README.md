# rollun-dic

---
## [Оглавление](https://github.com/rollun-com/rollun-skeleton/blob/master/docs/Contents.md)

---

# Справка

## Иньекция зависимости
Через сеттер метод - если существует сеттер для свойста, при иньекции будет использован он. 
через свойство/атрибут обьекта - зависимость будет иньецирована через свойство, в случае если соответсвующий сеттер не был найден.

public свойства - инициализация свойств в инстансе с учетом иерархии наследования
protected свойства -инициализация свойств в инстансе с учетом иерархии наследования
private свойства - инициализация свойств в инстансе без иерархии наследования


## Загрузка зависимостей и типизация параметров 
Если параметр типизирован простым типом то такой параметр не будет получаться из контейнера.
> простые типы (int,string,float,array,resource,)

Если параметр типизирован именем класса или интерфейса, то такой сервис будет загружен из контейнера и в случае неудачи 
будет выброшено исключение.

В случае если параметр был не типизирован, то сервис попытается загрузится из контейнера, 
а в случае неудачи будет использовано значение по умолчанию.

## Методы и специфика вызовов 

### init
Инициализирует сервис зависимостями указаными в методе `__construct`.   
Вызывает родительский конструктор в случае наличия такового и иньецирует зависимости в него. 
Так же, позволяет пробросить зависимость в родительский конструктор.   
Для этого нужено в параметры метода передать массив где укзать мапинг.   
В качестве *ключа* массива указываем имя параметра(сервиса) в конструкторе, 
а в качестве значения - имя параметра конструктора родительского класса.

### setConstructParams
Инициализирует сервис зависимостями указаными в методе `__construct`.
> Родительский конструктор не вызывается

В качестве параметров можно передать конфиг маппинга.  
В качестве ключа - имя параметра конструктора, а в качестве значения имя сервиса которое нужно загрузить.  

### initWakeup
Позволяет инициализировать сервис зависимостями.  
В качетсве параметра можно передать конфиг мапина.  
В качестве ключа - имя свойства/атрибут класа, а в качестве значения имя сервиса которое нужно загрузить.

### runParentConstruct
Вызывает родительский конструктор  
В качетсве параметра можно передать конфиг мапина.  
В качестве ключа - имя параметра конструктора(родительского класса), а в качестве значения имя сервиса которое нужно загрузить.  


Каркас для создания приложений. 

* [Стандарты](https://github.com/rollun-com/rollun-skeleton/blob/master/docs/Standarts.md)

# DI - InsideConstruct

##Быстрый старт

###Обычная практика

Пусть у нас есть класс, принимающий 3 сервиса в качестве зависимостей:

```
    class Class1
    {
        public $propA;
        public $propB;
        public $propC;

        public function __construct($propA = null, $propB = null, $propC = null)
        {
            $this->propA = $propA;
            $this->propB = $propB;
            $this->propC = $propC;
        }
    }

    /* @var $contaner ContainerInterface */
    global $contaner;
    $propA = $contaner->has('propA') ? $contaner->get('propA') : null;
    $propB = $contaner->has('propB') ? $contaner->get('propB') : null;
    $propC = $contaner->has('propC') ? $contaner->get('propC') : null;

    new Class1($propA, $propB, $propC);
```

Мы получили из контейнера зависимости и присвоили их одноименным свойствам объекта.

###Теперь то-же самое с использованием `InsideConstruct::init()`:

Если имя параметра соответствует имени сервиса и имени свойства объекта:

```
    class Class1
    {

        public $propA;
        public $propB;
        public $propC;

        public function __construct($propA = null, $propB = null, $propC = null)
        {
            InsideConstruct::init();
        }

    }

    new Class1();
```

Все три сервиса будут инициализированы сервисами из `$containr` как в примере выше.  
Вызов `InsideConstruct::init` не изменяет переданные в констрактор параметры.  
Если у параметров констрактора указаны тип или интерфейс, то сервисы, полученные вызовом 
`InsideConstruct::init()` будут проверены на соответствие.  
Инициализируются `Public`, `Protected`, и `Private` свойства объекта, а так же свойства родителя, 
если они не были инициализированы ранее. После будет вызван конструктор родителя и туда будет переданы сервисы.

Не инициализируются `Static` свойства и `Private` свойства предков.
 
##Использование

### Что возвращает метод `InsideConstruct::init();`
Возвращается массив `['param1Name'=>value1', 'param2Name' => 'value2', ...]`

### Как перекрыть умолчания
Если так:

            new Class1(new stdClass(), null);
то только один (последний) параметр будет инициализирован сервисом `$contaner->get('propC')`.  
Два других получат значения `new stdClass(`) и `null`. Но присваивания свойствам объекта или вызовы сеттеров (см. далее) отработают для всех параметров. 


### Сеттеры  (`$this->setPropA($value)`)
Если для параметра констрактора определен соответствующий (по имени) сеттер - он будет вызван.
Сеттеры имеют приоритет над свойствами. Если для параметра есть и сеттер и свойство, то будет вызван сеттер, а присваивание свойству не будет произведено.

### А если наследование?
Предположим у нас есть базовый класс:

```
	class Class0
	{
		public $propA;
		public $propB;
	
	    public function __construct($newPropA = null, $propB= null)
	    {
	        //do some...
		}
	}

	$class0 = new Class0;        // $class0->propA = $container->get('propA');
```
, а нам нужно изменить используемый сервис:  

```
 $class0->propA = $container->get('newPropA');
 $class0->propB = $container->get('propB');
```
  
Можно так:

```
	class Class1 extends Class0
	{
	    public function __construct($newPropA = null)
	    {
	          InsideConstruct::init(['newPropA' => 'propA']);  
		}
	};
```

Или же используя метод `init()` мы можем инициализировать наши зависимости и зависимости родительского класа.
Если в конструкторе нашего класcа имеется имя того же сервиса что и в конструкторе родительского класса то 
зависимость пробрасывается - будет передана в конструктор родителя в качестве параметра.
Так же метод `init()` пренимает массив подстановки параметров, в случае если наследник переопределяет зависимость родителя.
В такком случае можно передать массив в котором **ключ будет содержать имя переопределенной зависимости, а значение имя изначальной зависимости**.

## А если нужно в промежутке что то сделать ?
```
	class Class0
	{
		public $propA;
	
	    public function __construct($propA = null)
	    {

		}
	}

	$class0 = new Class0;        // $class0->propA = $container->get('propA');
```
, а нам нужно изменить используемый сервис:  
    ``` 
        $propB = $container->get('propB');
        $class0->propA = $propB->getPropA()
    ```  
Можно так:

```
	class Class1 extends Class0
	{
	    public function __construct($propB = null)
	    {
	            $params = InsideConstruct::setConstructParams();
	            $propA = ($params['propB'])->getPropA();
				InsideConstruct::runParentConstruct(['propA' => $propA]);
		}
	};
```

Мы моежем использовать метод `runParentConstruct()` для того что бы инициализировать родительские зависимости через конструктор,
Так же мы можем передавать в них массив содержащий те поля которые мы явно хотим передать в конструктор родительского класcа.

А вызвав метод `setConstructParams()` мы инициализируем свои зависимости. Так же можно передать массив в качесвте парамтера
где ключами указать имена переменных класса для который есть сетеры и которые хотите ини циализтировать, 
а в качестве значения передать имя сервиса.

### Параметры вызова
В прошлом примере `InsideConstruct::runParentConstruct(['propA' => $propA]);` добавлен параметр вызова `['propA' => $propA]`.
Это сделано для того что бы мы могли передать в конструктор родителя, заранее определенные параметры.


### Еще раз коротко о главном
Если есть соответствующий сеттер или свойство - значение будет присвоено.   
Если параметр передан (даже если `NULL`) - сервис из контейнера загружен не будет.   
Если параметр не передан, сервис из контейнера буде загружен если есть сеттер или свойство.   

