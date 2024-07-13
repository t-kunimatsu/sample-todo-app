import { useDroppable } from "@dnd-kit/core";
import { SortableContext, verticalListSortingStrategy } from "@dnd-kit/sortable";
import { AddCircleOutline } from "@mui/icons-material";
import { Box, Button, Typography } from "@mui/material";
import Card from "./Card";
import CardDialog from "./CardDialog";
import { useTodoBoard } from "./useTodoBoard";
import { Task, Status } from "@/Apis/tasks";
import { useCallback } from "react";

export type ColumnProps = {
  id: Status;
  title: string;
  tasks: Task[];
  showAddTask: boolean;
};

const Column: React.FC<ColumnProps> = (props) => {
  const { id, title, tasks, showAddTask } = props;
  const { setNodeRef } = useDroppable({ id: id });
  const {
    addCard,
    editCard,
    currentColumnId,
    setCurrentColumnId,
    currentCard,
    setCurrentCard,
    dialogOpen,
    setDialogOpen,
    dialogMode,
    setDialogMode,
    resetCardDialogForm,
  } = useTodoBoard();

  const handleDialogOpen = (columnId: Status) => {
    setDialogMode("add");
    setCurrentColumnId(columnId);
    setCurrentCard({ title: "", status: id });
    resetCardDialogForm && resetCardDialogForm({ title: "" });
    setDialogOpen(true);
  };

  // TODO >> ダイアログ側で直接呼び出せばよい（というか閉じるだけで他に処理がないなら実装自体不要）
  const handleDialogClose = () => {
    setDialogOpen(false);
  };

  const saveCard = useCallback(
    (title: string) => {
      if (dialogMode === "add") {
        addCard(currentColumnId, title);
        return;
      }
      editCard((currentCard as Task).id, title, currentColumnId);
    },
    [dialogMode, currentColumnId, currentCard, addCard, editCard]
  );

  const handleSaveCard = useCallback(
    (title: string) => {
      saveCard(title);
      handleDialogClose();
    },
    [saveCard, handleDialogClose]
  );

  return (
    <SortableContext id={id} items={tasks} strategy={verticalListSortingStrategy}>
      <Box
        ref={setNodeRef}
        sx={{
          flex: "1",
          background: "rgba(245,247,249,1.00)",
          marginRight: "10px",
          padding: "8px",
          minHeight: "90dvh",
        }}
      >
        <Box
          sx={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
            height: "24px",
            padding: "2px",
            marginBottom: "16px",
          }}
        >
          <Typography
            variant="h6"
            sx={{
              fontWeight: "800",
              color: "#575757",
            }}
          >
            {title}
          </Typography>
          {showAddTask && (
            <Button
              variant="contained"
              onClick={() => handleDialogOpen(id)}
              sx={{ padding: "4px", margin: "4px", minWidth: "32px" }}
            >
              <AddCircleOutline />
            </Button>
          )}
        </Box>
        {tasks.map((task) => (
          <Box key={task.id} sx={{ display: "flex", alignItems: "center", padding: "4px" }}>
            <Card {...task} />
          </Box>
        ))}
      </Box>
      {/* TODO >> カラムごとに持つ必要ないかも・・・ */}
      {/* TODO >> 逆にkeyを指定してそれぞれに持たせる形にすれば、ごちゃごちゃした制御を削除できるはず・・・ */}
      <CardDialog open={dialogOpen} onClose={handleDialogClose} onSave={handleSaveCard} />
    </SortableContext>
  );
};

export default Column;
