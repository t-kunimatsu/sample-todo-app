import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import { userEvent } from "@testing-library/user-event";
import { http, HttpResponse } from "msw";
import { setupServer } from "msw/node";
import Todo from "@/Pages/Todo";
import { useTodoBoard } from "@/Components/useTodoBoard";

const tasks = [
  { id: 1, title: "Task 1", status: "todo" },
  { id: 2, title: "Task 2", status: "doing" },
];

const server = setupServer(
  http.get("/api/v1/tasks", () => {
    return HttpResponse.json(tasks);
  })
);

beforeAll(() => server.listen({ onUnhandledRequest: "error" }));
afterAll(() => server.close());
afterEach(() => server.resetHandlers());

describe("TODO画面コンポーネントのインテグレーションテスト", () => {
  it("描画されること", async () => {
    const { asFragment } = render(<Todo />);
    await waitFor(() => {
      expect(screen.queryByText("ROADING...")).not.toBeInTheDocument();
    });
    expect(asFragment()).toMatchSnapshot();
    expect(screen.getByText("Task 1")).toBeInTheDocument();
    expect(screen.getByText("Task 2")).toBeInTheDocument();
  });

  it("タスク追加ボタン〜ダイアログオープン〜タスク入力〜タスク保存までの一連の処理が正常に動くこと", async () => {
    const responseNewTask = { id: 3, title: "New Task", status: "todo" };
    server.use(
      http.post("/api/v1/tasks", () => {
        return HttpResponse.json(responseNewTask);
      })
    );
    render(<Todo />);
    await waitFor(() => {
      expect(screen.queryByText("ROADING...")).not.toBeInTheDocument();
    });
    const addButton = screen.getByTestId("add-button-todo");
    await userEvent.click(addButton);
    expect(screen.getByText("追加")).toBeInTheDocument();
    const inputNode = screen.getByPlaceholderText("やること");
    await userEvent.type(inputNode, "New Task");
    expect(inputNode).toHaveValue("New Task");
    const saveButton = screen.getByTestId("save-button");
    await userEvent.click(saveButton);

    const columns = useTodoBoard.getState().columns;
    const todoTasks = columns.find((column) => column.id === "todo")?.tasks;
    const doingTasks = columns.find((column) => column.id === "doing")?.tasks;
    expect(todoTasks).length(2);
    if (todoTasks) {
      expect(todoTasks[0]).toEqual({ id: 1, title: "Task 1", status: "todo" });
      expect(todoTasks[1]).toEqual({ id: 3, title: "New Task", status: "todo" });
    }
    expect(doingTasks).length(1);
    if (doingTasks) {
      expect(doingTasks[0]).toEqual({ id: 2, title: "Task 2", status: "doing" });
    }
    expect(screen.getByText("Task 1")).toBeInTheDocument();
    expect(screen.getByText("Task 2")).toBeInTheDocument();
    expect(screen.getByText("New Task")).toBeInTheDocument();
  });

  it("タスク編集ボタン〜ダイアログオープン〜タスク変更〜タスク保存までの一連の処理が正常に動くこと", async () => {
    server.use(
      http.patch("/api/v1/tasks/2", () => {
        return HttpResponse.json("", {
          status: 200,
        });
      })
    );
    render(<Todo />);
    await waitFor(() => {
      expect(screen.queryByText("ROADING...")).not.toBeInTheDocument();
    });
    const editButton = screen.getByTestId("edit-button-2");
    await userEvent.click(editButton);
    expect(screen.getByText("編集")).toBeInTheDocument();
    const inputNode = screen.getByPlaceholderText("やること");
    await userEvent.type(inputNode, " Update");
    expect(inputNode).toHaveValue("Task 2 Update");
    const saveButton = screen.getByTestId("save-button");
    await userEvent.click(saveButton);

    const columns = useTodoBoard.getState().columns;
    const todoTasks = columns.find((column) => column.id === "todo")?.tasks;
    const doingTasks = columns.find((column) => column.id === "doing")?.tasks;
    expect(todoTasks).length(1);
    if (todoTasks) {
      expect(todoTasks[0]).toEqual({ id: 1, title: "Task 1", status: "todo" });
    }
    expect(doingTasks).length(1);
    if (doingTasks) {
      expect(doingTasks[0]).toEqual({ id: 2, title: "Task 2 Update", status: "doing" });
    }
    expect(screen.getByText("Task 1")).toBeInTheDocument();
    expect(screen.getByText("Task 2 Update")).toBeInTheDocument();
  });

  it("タスク削除ボタンの処理が正常に動くこと", async () => {
    server.use(
      http.delete("/api/v1/tasks/2", () => {
        return HttpResponse.json("", {
          status: 200,
        });
      })
    );
    render(<Todo />);
    await waitFor(() => {
      expect(screen.queryByText("ROADING...")).not.toBeInTheDocument();
    });
    const deleteButton = screen.getByTestId("delete-button-2");
    await userEvent.click(deleteButton);

    const columns = useTodoBoard.getState().columns;
    const todoTasks = columns.find((column) => column.id === "todo")?.tasks;
    const doingTasks = columns.find((column) => column.id === "doing")?.tasks;
    expect(todoTasks).length(1);
    if (todoTasks) {
      expect(todoTasks[0]).toEqual({ id: 1, title: "Task 1", status: "todo" });
    }
    expect(doingTasks).length(0);
    expect(screen.getByText("Task 1")).toBeInTheDocument();
    expect(screen.queryByText("Task 2")).not.toBeInTheDocument();
  });
});
