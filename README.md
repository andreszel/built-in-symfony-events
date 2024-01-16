Testowy projekt pokazujący użycie wbudowanych eventów w Symfony. Projekt został utworzony na potrzeby utrwalenia sobie i ewentualnie osobom zainteresowanym wiedzy w tym konkretnym zagadnieniu. Ucząc się pomyślałem, że jest to również dobry sposób na utrwalenie informacji w tym temacie.

Przechodząc do konkretów to w Symfony mamy kilka rodzajów eventów:
1. HTTP events
2. Form events
3. Doctrine events


Dalej opiszę to co dla mnie jest istotne. Dodam, że informacje czerpałem przede wszystkim z dokumentacji.

Ad. 1

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

W profiler w zakładce Performance zaznaczając tylko event_listener i czas na 0 ms mamy pokazaną kolejność wykonywania events w request od początku do końca.

W przykładowym żądaniu mamy kolejno:
kernel.request
karnel.controller
kernel.controller_arguments
kernel.response
kernel.terminated

Dla każdego event-u wg kolejności wykonują się kolejno listeners wg priority, od najwyższego do najniższego.

WAŻNE:
Jeżeli na jakimś poziomie, np. kernel.request zostanie utworzony Response, to propagacja zostaje zastrzymana, co oznacza, że listeners z niższego poziomu, które miały wykonywać się dalej nie zostaną już wykonane. Dokładnie taka zasada obowiązuje na każdym etapie rozwiązywania requestu. Po kernel.request powinny wykonać się eventy z kernel.controller, pod warunkiem, że żaden z listenerów kernel.request nie utworzył Response. kernel.controller to część kodu aplikacji odpowiedzialna za utworzenie i zwrócenie odpowiedzi dla żądania. Określenie kontrolera dla requestu jest realizowane w ControllerResolver, który jest argumentem konstruktora HttpKernel. Wszystkie listenery z kernel.controller wykonywane są przed wykonaniem kontrolera. Kolejna grupa to sprawdzenie i rozwiązanie przekazywanych parametrów do kontrolera, jeżeli przekazujemy parametry, które powinny być przekazane to akcja postępuje dalej, czyli przechodzimy już do wykonania kontrollera. Kontroller buduje odpowiedź w postaci HTML, JSON, XML lub cokolwiek innego. Zazwyczaj kontroler zwraca obiekt Response, jeżeli tak jest to przechodzimy do kernel.response, a jeżeli nie to jest jeszcze trochę więcej pracy, tzn. przechodzimy do kernel.view. Jeżeli kontroler nie zwróci czegokolwiek, czyli zwróci null to automatycznie rzucany jest wyjątek. Zakładając, że nie był zwrócony obiekt Response, więc nasz request musi przejść jeszcze przez kernel.view, którego zadaniem jest przekształcenie nie Response na obiekt Response. Nie zwrócenie Response może być użyteczne, jeżeli chcemy użyć warstwy widoku, czyli zamiast zwracać Response zwracamy dane, które reprezentują daną stronę. Listener może przechwycić te dane do utworzenia Response, który będzie w wymaganym formacie, np. HTML, JSON, XML. Listener zrobi to co twórcy aplikacji bedzie potrzebne. Kolejnym etapem jest kernel.response, gdzie mamy możliwość modyfikacji Response tuż przed jego wysłaniem. Typowymi zadaniami w tym evencie mogą być: modyfikacja nagłówków, dodawanie plików coockie lub nawet zmiana treści samej odpowiedzi, np. poprzez wstrzyknięcie JS-a przed końcem body w odpowiedzi. Ostatnim etapem procesu HTTP jest kernel.terminated, które jest wykonywane po metodzie HttpKernel::handle() oraz po wysłaniu odpowiedzi do użytkownika. Podczas tego eventu możemy wykonać zadania typu wysłanie maila klientowi, aby nie opóźniać wysłania odpowiedzi do klienta. Do tego mamy jeszcze karnel.exception, czyli event, do którego można podpiąć listenery, które obsługują wyjątki. W Symfony metoda handle() jest owinięta blokiem try-catch, aby system reagował na wszystkie wyjątki, które są rzucane w systemie. Do każdego listenera przekazywany jest obiekt Exception Event, z którego możemy sobie pobrać info o oryginalnym wyjątku dzięki metodzie getThrowable(). Możemy sprawdzić typ wyjątku i w listenerze utworzyć własy Response. Możemy zastosować taki trik, żeby w potrzebnym miejscu rzucić nasz-customowy wyjątek, a w listenerze przechwycić go i odpowiednio zareagować. W tym evencie również obowiązuje zasada, że jeżeli ustawiamy już Response to propagacja jest wstrzymana i listenery z niższym priorytetem nie będą wykonane.