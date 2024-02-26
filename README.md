Zdarzenia w Symfony(aktualnie wersja 6.4)

Testowy projekt pokazujący użycie wbudowanych eventów w Symfony. Projekt został utworzony na potrzeby utrwalenia sobie i ewentualnie osobom zainteresowanym wiedzy w tym konkretnym zagadnieniu. Ucząc się pomyślałem, że jest to również dobry sposób na utrwalenie informacji w tym temacie.

Przechodząc do konkretów to w Symfony mamy kilka rodzajów eventów:
1. HTTP Events
2. Form Events
3. Doctrine Events


Dalej opiszę to co dla mnie jest istotne. Dodam, że informacje czerpałem przede wszystkim z dokumentacji Symfony dostępnej na stronie https://symfony.com/doc/current/index.html.


# HTTP Events

W Symfony podczas wykonywania Requestu wywoływane może być wiele zdarzeń, zarówno tych wbudowanych w Symfony jak i własnych. 
Aplikacja może nasłuchiwać oraz wywoływać takie zdarzenia. Możemy korzystać z wbudowanych możliwości oraz możemy tworzyć własne dispatchery, listenery czu subscribery.
Framework korzysta z komponentu HttpKernel podczas obsługi żądania HTTP co powoduje wywoływanie pewnych zdarzeń, których możemy użyć do zmodyfikowania sposobu obsługi żądania i sposobu zwracania odpowiedzi.

Zdarzeniami wbudowanymi w Symfony wywoływanymi podczas przetwarzania żądania są:

    Name	                        KernelEvents Constant	            Argument passed to the listener
    kernel.request                  KernelEvents::REQUEST -             RequestEvent
    kernel.controller               KernelEvents::CONTROLLER -          ControllerEvent
    kernel.controller_arguments     KernelEvents::CONTROLLER_ARGUMENTS	ControllerArgumentsEvent
    kernel.view                     KernelEvents::VIEW	                ViewEvent
    kernel.response                 KernelEvents::RESPONSE	            ResponseEvent
    kernel.finish_request           KernelEvents::FINISH_REQUEST	    FinishRequestEvent
    kernel.terminate                KernelEvents::TERMINATE	            TerminateEvent
    kernel.exception                KernelEvents::EXCEPTION	            ExceptionEvent

Ich kolejność nie jest przypadkowa, ponieważ w takiej kolejności wykonywane są w Symfony. Ważnym jest, że nie wszystkie eventy są wykonywane w każdym żądaniu.

W profiler(pakiet symfony/profiler-pack) w zakładce Performance zaznaczając tylko event_listener i czas na 0 ms mamy pokazaną kolejność wykonywania events w wybranym żądaniu (request) od początku do końca.

### W przykładowym żądaniu mamy kolejno:
- kernel.request
- karnel.controller
- kernel.controller_arguments
- kernel.response
- kernel.terminated

Dla każdego event-u wg kolejności wykonują się kolejno listeners wg priority, od najwyższego do najniższego.

### Wyjaśnienie procesu przetwarzania requestu:
Jeżeli na jakimś poziomie, np. kernel.request zostanie utworzony Response, to propagacja zostaje zatrzymana(nie wykonują się kolejne listenery), co oznacza, że listeners z niższego poziomu, które miały wykonywać się dalej nie zostaną już wykonane. Dokładnie taka zasada obowiązuje na każdym etapie rozwiązywania requestu. Po kernel.request powinny wykonać się eventy z kernel.controller, pod warunkiem, że żaden z listenerów kernel.request nie utworzył Response. kernel.controller to część kodu aplikacji odpowiedzialna za utworzenie i zwrócenie odpowiedzi dla żądania. Określenie kontrolera dla requestu jest realizowane w ControllerResolver, który jest argumentem konstruktora HttpKernel. Wszystkie listenery z kernel.controller wykonywane są przed wykonaniem kontrolera. Kolejna grupa to sprawdzenie i rozwiązanie przekazywanych parametrów do kontrolera, jeżeli przekazujemy parametry, które powinny być przekazane to akcja postępuje dalej, czyli przechodzimy już do wykonania kontrollera. Kontroller buduje odpowiedź w postaci HTML, JSON, XML lub cokolwiek innego. Zazwyczaj kontroler zwraca obiekt Response, jeżeli tak jest to przechodzimy do kernel.response, a jeżeli nie to jest jeszcze trochę więcej pracy, tzn. przechodzimy do kernel.view. Jeżeli kontroler nie zwróci czegokolwiek, czyli zwróci null to automatycznie rzucany jest wyjątek. Zakładając, że nie był zwrócony obiekt Response, więc nasz request musi przejść jeszcze przez kernel.view, którego zadaniem jest przekształcenie nie Response na obiekt Response. Nie zwrócenie Response może być użyteczne, jeżeli chcemy użyć warstwy widoku, czyli zamiast zwracać Response zwracamy dane, które reprezentują daną stronę. Listener może przechwycić te dane do utworzenia Response, który będzie w wymaganym formacie, np. HTML, JSON, XML. Listener zrobi to co twórcy aplikacji bedzie potrzebne. Kolejnym etapem jest kernel.response, gdzie mamy możliwość modyfikacji Response tuż przed jego wysłaniem. Typowymi zadaniami w tym evencie mogą być: modyfikacja nagłówków, dodawanie plików coockie lub nawet zmiana treści samej odpowiedzi, np. poprzez wstrzyknięcie JS-a przed końcem body w odpowiedzi. Ostatnim etapem procesu HTTP jest kernel.terminated, które jest wykonywane po metodzie HttpKernel::handle() oraz po wysłaniu odpowiedzi do użytkownika. Podczas tego eventu możemy wykonać zadania typu wysłanie maila klientowi, aby nie opóźniać wysłania odpowiedzi do klienta. Do tego mamy jeszcze karnel.exception, czyli event, do którego można podpiąć listenery, które obsługują wyjątki. W Symfony metoda handle() jest owinięta blokiem try-catch, aby system reagował na wszystkie wyjątki, które są rzucane w systemie. Do każdego listenera przekazywany jest obiekt Exception Event, z którego możemy sobie pobrać info o oryginalnym wyjątku dzięki metodzie getThrowable(). Możemy sprawdzić typ wyjątku i w listenerze utworzyć własny Response. Możemy zastosować taki trik, żeby w potrzebnym miejscu rzucić nasz-customowy wyjątek, a w listenerze przechwycić go i odpowiednio zareagować. W tym evencie również obowiązuje zasada, że jeżeli ustawiamy już Response to propagacja jest wstrzymana i listenery z niższym priorytetem nie będą wykonane.

# Form Events

Korzystając ze zdarzeń formularza mamy możliwość modyfikacji informacji lub pól w różnych momentach(krokach) przepływu pracy(realiacji formularza): od wypełniania formularza do wysyłki - przekazania danych z żądania.
W cyklu życia formularz mamy do dyspozycji 2 momenty, kiedy dane mogą być aktualizowane.
1. Podczas budowania formularza kiedy korzystamy z metody setData()
2. Podczas obsługi formularza (Form::handleRequest()) na podstawie wartości wprowadzonych przez użytkownika

Podczas budowania formularza wywoływane są 2 zdarzenia:
form.pre_set_data - FormEvents::PRE_SET_DATA - tuż przed wywołaniem metody setData()
form.post_set_data - FormEvents::POST_SET_DATA - tuż po wywołaniu metody setData()

Podczas wysyłania obsługi formularza, kiedy wywołana zostaje metoda Form::handleRequest() lub Form::submit() są wywoływane 3 zdarzenia:
form.pre_submit - FormEvents::PRE_SUBMIT - tuż przed rozpoczęciem metody submit()
form.submit	 - FormEvents::SUBMIT - tuż przed przekształceniem znormalizowanych danych z powrotem do modelu i danych widoku
form.post_submit - FormEvents::POST_SUBMIT - tuż po metodzie submit() po denormalizacji danych modelu i widoku

https://symfony.com/doc/current/form/events.html#2-submitting-a-form-formevents-pre-submit-formevents-submit-and-formevents-post-submit


# Doctrine Events

Doctrine to zestaw bibliotek używanych przez Symfony do pracy z bazami danych, zapewnia lekki system zdarzeń do aktualizacji encji podczas działania aplikacji. Przykładowo pola updatedAt, createdAt można w łatwy sposób zaktualizować tuż przez Updatem lub tuż przed Insertem.
Zdarzeniami, które mogą być wywołane są: prePersist, postPersist, preUpdate, postUpdate. Wyróżnić możemy 3 różne sposoby nasłuchiwania tych zdarzeń:

    1. Lifecycle callbacks - publiczna metoda, prosta logika, tylko w konkretnej encji, nie mogą używać serwisów.
#[ORM\HasLifecycleCallbacks], #[ORM\PrePersist], #[ORM\PreUpdate] => setCreatedAtValue()

    2. Entity listener - publiczna metoda, złożona logika, tylko dla konkretnej encji, może używać serwisów.

Tworzymy klasę i odpowiednio powiązujemy ją z eventem.

<?php
namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\Event\PostUpdateEventArgs;

class UserChangedNotifier
{
    public function postUpdate(User $user, PostUpdateEventArgs $event): void
    {
        // ... do something to notify the changes
    }
}

- dodajemy atrybut przed klasą

    namespace App\EventListener;

    // ...
    use App\Entity\User;
    use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
    use Doctrine\ORM\Events;

    #[ORM\HasLifecycleCallbacks], #[ORM\PrePersist], #[ORM\PreUpdate] => setCreatedAtValue()

- lub alternatywnie w pliku services.yaml dodajemy konfigurację dla tej klasy:

    App\EventListener\UserChangedNotifier:
        tags:
            -
                # these are the options required to define the entity listener
                name: 'doctrine.orm.entity_listener'
                event: 'postUpdate'
                entity: 'App\Entity\User'


    3. Lifecycle listeners - tak samo jak entity listener, ale mogą być wywoływane dla wszystkich encji, idealne do udostępniania logiki między 
    encjami.

    - definiujemy klasę

    class SearchIndexer
    {
        public function postPersist(PostPersistEventArgs $args): void
        {
            $entity = $args->getObject();

            if (!$entity instanceof Product) {
                return;
            }

            $entityManager = $args->getObjectManager();
            // ... do something with the Product entity
        }
    }

    - dodajemy atrybut

    #[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]

    Ciekawy wpis:
    https://hugo.alliau.me/posts/2023-11-12-listen-to-doctrine-events-on-entities-given-a-php-attribute.html

    4. Lifecycle subscribers - deprecated od Symfony 6.3



Wydajność tych mechanizmów zależy od ilościa encji, najszybciej działa lifecycle callbacks, a najwolniej lifecycle listeners, pośrodku jest entity listener. To tyle jeżeli chodzi o podstawy o zdarzeniach Doctrine, kiedy używać ich w aplikacji Symfony. Wyjaśnienie dotyczy listenerów i subscriberów dla Doctrine ORM.




https://symfony.com/doc/6.4/doctrine/events.html

