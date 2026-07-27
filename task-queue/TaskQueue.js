/**
 * Kolejka zadań asynchronicznych z priorytetami i limitem równoległości.
 *
 * - add(fn, priority)  — dodaje zadanie (fn zwraca Promise); wyższy priorytet = ważniejsze.
 * - run(concurrency)   — uruchamia zadania, maksymalnie `concurrency` na raz, aż kolejka
 *                        się opróżni (łącznie z zadaniami dodanymi w trakcie działania run()).
 * - getStats()         — { pending, running, completed, failed }.
 *
 * Błąd pojedynczego zadania nie przerywa kolejki: jest logowany, licznik `failed`
 * rośnie, a worker od razu bierze się za kolejne zadanie.
 */
class TaskQueue {
    #queue = [];
    #stats = { pending: 0, running: 0, completed: 0, failed: 0 };

    add(fn, priority = 0) {
        if (typeof fn !== 'function') {
            throw new TypeError('TaskQueue.add(fn, priority): fn musi być funkcją zwracającą Promise');
        }

        this.#queue.push({ fn, priority });
        this.#stats.pending++;

        return this;
    }

    // Wyciąga z kolejki zadanie o najwyższym priorytecie (FIFO przy remisie,
    // bo Array#findIndex zwraca pierwsze trafienie).
    #dequeueHighestPriority() {
        if (this.#queue.length === 0) {
            return undefined;
        }

        let bestIndex = 0;
        for (let i = 1; i < this.#queue.length; i++) {
            if (this.#queue[i].priority > this.#queue[bestIndex].priority) {
                bestIndex = i;
            }
        }

        return this.#queue.splice(bestIndex, 1)[0];
    }

    async #worker() {
        let task;
        while ((task = this.#dequeueHighestPriority())) {
            this.#stats.pending--;
            this.#stats.running++;

            try {
                await task.fn();
                this.#stats.completed++;
            } catch (error) {
                this.#stats.failed++;
                console.error('[TaskQueue] Zadanie zakończone błędem:', error);
            } finally {
                this.#stats.running--;
            }
        }
    }

    async run(concurrency = 3) {
        const workerCount = Math.max(1, concurrency);
        const workers = Array.from({ length: workerCount }, () => this.#worker());

        await Promise.all(workers);

        return this.getStats();
    }

    getStats() {
        return { ...this.#stats };
    }
}

export default TaskQueue;
