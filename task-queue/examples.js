import TaskQueue from './TaskQueue.js';

const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

// ---------------------------------------------------------------------------
// Przykład 1: podstawowe użycie z priorytetami i limitem równoległości.
// Zadania o wyższym priorytecie są wykonywane w pierwszej kolejności, ale
// z concurrency=2 najwyżej dwa zadania biegną jednocześnie — reszta czeka.
// ---------------------------------------------------------------------------
async function example1_basicPriority() {
    console.log('\n=== Przykład 1: priorytety + concurrency ===');

    const queue = new TaskQueue();
    const order = [];

    queue.add(async () => { await wait(30); order.push('niski (0)'); }, 0);
    queue.add(async () => { await wait(10); order.push('wysoki (10)'); }, 10);
    queue.add(async () => { await wait(20); order.push('sredni (5)'); }, 5);

    const stats = await queue.run(2);

    console.log('Kolejność zakończenia:', order);
    console.log('Statystyki:', stats);
}

// ---------------------------------------------------------------------------
// Przykład 2: zadania błędne nie przerywają kolejki.
// Jedno z zadań rzuca wyjątkiem — zostaje zaliczone do `failed`, a pozostałe
// zadania i tak zostają wykonane do końca.
// ---------------------------------------------------------------------------
async function example2_errorsDontStopTheQueue() {
    console.log('\n=== Przykład 2: odporność na błędy ===');

    const queue = new TaskQueue();

    queue.add(async () => 'ok-1');
    queue.add(async () => { throw new Error('celowy błąd zadania #2'); });
    queue.add(async () => 'ok-3');

    const stats = await queue.run(3);

    console.log('Statystyki (1 z 3 zadań miał zawieść):', stats);
    // -> { pending: 0, running: 0, completed: 2, failed: 1 }
}

// ---------------------------------------------------------------------------
// Przykład 3: dynamiczne dokładanie zadań w trakcie działania run().
// Workery cały czas odpytują kolejkę, więc add() wywołane już po starcie
// run() (ale przed jego zakończeniem) też zostanie obsłużone.
// ---------------------------------------------------------------------------
async function example3_dynamicallyAddingTasks() {
    console.log('\n=== Przykład 3: dokładanie zadań w locie ===');

    const queue = new TaskQueue();

    queue.add(async () => {
        await wait(10);
        // Ten task, uruchomiony jako pierwszy, dokłada kolejne zadanie do
        // wciąż działającej kolejki.
        queue.add(async () => 'dolozone-w-locie', 100);
    });

    const runPromise = queue.run(1);

    console.log('Statystyki tuż po starcie run():', queue.getStats());

    const stats = await runPromise;
    console.log('Statystyki po zakończeniu run():', stats);
}

await example1_basicPriority();
await example2_errorsDontStopTheQueue();
await example3_dynamicallyAddingTasks();
