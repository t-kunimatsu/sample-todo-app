import useSWR, { SWRResponse } from "swr";

export type Status = "todo" | "doing" | "done";

export type Task = {
  todo: Card[];
  doing: Card[];
  done: Card[];
};

export type Card = {
  id: number;
  title: string;
  status: Status;
};

export type NewCard = {
  title: string;
  status: Status;
};

export const getTask = async (): Promise<Task> => {
  const response = await fetch("/api/v1/tasks");
  if (!response.ok) {
    // TODO >> トースト実装する
    throw new Error("Failed to fetch cards");
  }
  return await response.json();
};

export const postTask = async (card: NewCard): Promise<Card> => {
  const { title, status } = card;
  const response = await fetch("/api/v1/tasks", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(card),
  });
  if (!response.ok) {
    // TODO >> トースト実装する
    throw new Error("Failed to fetch cards");
  }
  return await response.json();
};
