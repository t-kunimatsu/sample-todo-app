import { render, screen, waitFor } from "@testing-library/react";
import { http, HttpResponse } from "msw";
import { setupServer } from "msw/node";
import Todo from "@/Pages/Todo";

const server = setupServer();
beforeAll(() => server.listen({ onUnhandledRequest: "error" }));
afterAll(() => server.close());
afterEach(() => server.resetHandlers());

describe("Todo component", () => {
  it("renders tasks successfully", async () => {
    const tasks = [
      { id: 1, title: "Task 1", status: "todo" },
      { id: 2, title: "Task 2", status: "doing" },
    ];
    server.use(
      http.get("/api/v1/tasks", () => {
        return HttpResponse.json(tasks);
      })
    );

    render(<Todo />);

    await waitFor(() => {
      expect(screen.queryByText("ROADING...")).not.toBeInTheDocument();
    });
    expect(screen.getByText("Task 1")).toBeInTheDocument();
    expect(screen.getByText("Task 2")).toBeInTheDocument();
  });
});
